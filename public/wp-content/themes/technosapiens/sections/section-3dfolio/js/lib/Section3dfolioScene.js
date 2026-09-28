import {
    AmbientLight,
    CanvasTexture,
    CircleGeometry,
    Color,
    DirectionalLight,
    DoubleSide,
    Group,
    MathUtils,
    Mesh,
    MeshBasicMaterial,
    MeshLambertMaterial,
    PerspectiveCamera,
    PlaneGeometry,
    Raycaster,
    Scene,
    SRGBColorSpace,
    TextureLoader,
    Vector2,
    Vector3,
    WebGLRenderer
} from 'three'
import Section3dfolioPopup from './Section3dfolioPopup'

const TAU = Math.PI * 2

/**
 * Section 3D Folio scene
 * Standing hex prism with one case per 1:1 side face, idle yaw, drag, click-to-open and cursor tilt.
 */
export default class Section3dfolioScene {
    STAGE_SELECTOR = '.section-3dfolio__stage'
    CANVAS_HOST_SELECTOR = '[data-section-3dfolio-canvas]'
    POPUP_ROOT_SELECTOR = '[data-section-3dfolio-popup]'
    FACE_NAV_SELECTOR = '[data-section-3dfolio-face-nav]'
    FACE_BUTTON_SELECTOR = '[data-section-3dfolio-face]'
    FACE_BUTTON_ATTRIBUTE = 'data-section-3dfolio-face'
    READY_CLASS = 'section-3dfolio--ready'
    GRABBING_CLASS = 'section-3dfolio__stage--grabbing'
    CLICKABLE_CLASS = 'section-3dfolio__stage--clickable'
    BRAND_COLOR_PROPERTY = '--section-3dfolio-brand'
    BRAND_MARK_ATTRIBUTE = 'data-section-3dfolio-brand-mark'
    FALLBACK_BRAND_COLOR = 'purple'

    FACE_COUNT = 6
    FACE_WIDTH = 1
    FACE_ASPECT = 1
    CAMERA_FOV = 35
    // Low enough to look at the faces, high enough to still read the top
    CAMERA_ELEVATION_DEG = 20
    // Fraction of the limiting frustum axis the prism should fill
    CAMERA_FIT_FILL = 0.75
    MAX_PIXEL_RATIO = 2
    AMBIENT_INTENSITY = 1.4
    DIRECTIONAL_INTENSITY = 1.6
    // Square mark size as a fraction of the hex circumdiameter
    BRAND_MARK_SCALE = 0.95
    BRAND_MARK_LIFT = 0.002
    // Rasterize the SVG large enough that the top mark stays sharp under camera zoom
    BRAND_MARK_TEXTURE_SIZE = 1024

    IDLE_SPEED = 0.2
    DRAG_SPEED = 0.008
    CLICK_THRESHOLD_PX = 6
    INERTIA_DAMPING = 4
    INERTIA_MIN_VELOCITY = 0.01
    INERTIA_MAX_IDLE_MS = 100
    FOCUS_DAMPING = 6
    TILT_MAX_DEG = 6
    TILT_DAMPING = 5
    MAX_FRAME_SECONDS = 0.1

    /**
     * @param {HTMLElement} $section
     * @param {Array<{imageUrl: string, title: string, body: string}>} cases
     * @param {{prefersReducedMotion: boolean, supportsHover: boolean}} options
     */
    constructor($section = null, cases = [], options = {}) {
        if (!($section instanceof HTMLElement)) {
            throw new Error('Missing required parameter `$section`')
        }

        this.$section = $section
        this.$stage = $section.querySelector(this.STAGE_SELECTOR)
        this.$canvasHost = $section.querySelector(this.CANVAS_HOST_SELECTOR)
        this.$popupRoot = $section.querySelector(this.POPUP_ROOT_SELECTOR)
        this.$faceNav = $section.querySelector(this.FACE_NAV_SELECTOR)
        this.cases = cases.slice(0, this.FACE_COUNT)
        this.options = {
            prefersReducedMotion: false,
            supportsHover: false,
            ...options
        }

        this.state = {
            yaw: 0,
            velocity: 0,
            focusTargetYaw: null,
            tiltX: 0,
            tiltY: 0,
            tiltTargetX: 0,
            tiltTargetY: 0,
            pointer: null,
            isDragging: false,
            stageWidth: 0,
            stageHeight: 0,
            frameId: null,
            lastTime: 0
        }

        this.raycaster = new Raycaster()
        this.pointerNdc = new Vector2()
        this.anchor = new Vector3()
        this.faceMeshes = []
        this.clickableMeshes = []

        this.init()
    }

    /**
     * Keep the static placeholder when markup or WebGL is missing
     */
    init() {
        if (!(this.$stage instanceof HTMLElement) || !this.$canvasHost || !this.$popupRoot) {
            return
        }

        this.popup = new Section3dfolioPopup(this.$popupRoot, () => this.handlePopupClose())

        try {
            this.renderer = new WebGLRenderer({antialias: true, alpha: true})
        } catch (error) {
            console.log('WebGL unavailable for section-3dfolio:', error)
            return
        }

        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, this.MAX_PIXEL_RATIO))
        this.renderer.setClearColor(0x000000, 0)

        this.buildScene()

        this.$canvasHost.appendChild(this.renderer.domElement)
        this.$canvasHost.hidden = false

        if (this.$faceNav) {
            this.$faceNav.hidden = false
        }

        this.$section.classList.add(this.READY_CLASS)

        this.bindEvents()
        this.observeSize()
        this.observeVisibility()
    }

    buildScene() {
        const directionalLight = new DirectionalLight('white', this.DIRECTIONAL_INTENSITY)

        this.scene = new Scene()
        this.camera = new PerspectiveCamera(this.CAMERA_FOV, this.FACE_ASPECT, 0.1, 50)
        this.fitCamera(this.FACE_ASPECT)

        directionalLight.position.set(2, 4, 3)
        this.scene.add(new AmbientLight('white', this.AMBIENT_INTENSITY))
        this.scene.add(directionalLight)

        // Tilt wraps yaw so cursor parallax never fights idle/drag rotation
        this.tiltGroup = new Group()
        this.yawGroup = new Group()
        this.tiltGroup.add(this.yawGroup)
        this.scene.add(this.tiltGroup)

        this.buildPrism()
    }

    /**
     * Place the camera so every corner stays inside the canvas during yaw + max tilt
     * @param {number} aspect
     */
    fitCamera(aspect) {
        const elevation = MathUtils.degToRad(this.CAMERA_ELEVATION_DEG)
        const halfFovY = MathUtils.degToRad(this.CAMERA_FOV / 2)
        const halfFovX = Math.atan(Math.tan(halfFovY) * aspect)
        const faceHeight = this.FACE_WIDTH / this.FACE_ASPECT
        const circumRadius = this.FACE_WIDTH
        // Projected half-height of the prism from this elevation (top foreshortening + side)
        const projectedHalfHeight = (faceHeight / 2) * Math.cos(elevation) + circumRadius * Math.sin(elevation)
        // Diagonal tilt pushes corners out; pad both axes
        const tiltPad = 1 / Math.cos(MathUtils.degToRad(this.TILT_MAX_DEG) * Math.SQRT2)
        const fitHalfWidth = circumRadius * tiltPad / this.CAMERA_FIT_FILL
        const fitHalfHeight = projectedHalfHeight * tiltPad / this.CAMERA_FIT_FILL
        const distance = Math.max(
            fitHalfWidth / Math.tan(halfFovX),
            fitHalfHeight / Math.tan(halfFovY)
        )

        this.camera.position.set(
            0,
            Math.sin(elevation) * distance,
            Math.cos(elevation) * distance
        )
        this.camera.lookAt(0, 0, 0)
    }

    /**
     * Six 1:1 side planes around a regular hexagon (side = face width) plus top/bottom caps
     */
    buildPrism() {
        const brandColor = this.getBrandColor()
        const faceHeight = this.FACE_WIDTH / this.FACE_ASPECT
        const apothem = this.FACE_WIDTH * Math.sqrt(3) / 2
        const faceGeometry = new PlaneGeometry(this.FACE_WIDTH, faceHeight)
        const capGeometry = new CircleGeometry(this.FACE_WIDTH, this.FACE_COUNT)
        const brandMaterial = new MeshLambertMaterial({color: brandColor})
        const capMaterial = new MeshLambertMaterial({color: brandColor, side: DoubleSide})

        this.textureLoader = new TextureLoader()

        for (let faceIndex = 0; faceIndex < this.FACE_COUNT; faceIndex++) {
            const angle = faceIndex * TAU / this.FACE_COUNT
            const mesh = new Mesh(faceGeometry, brandMaterial)
            const caseData = this.cases[faceIndex]

            mesh.position.set(Math.sin(angle) * apothem, 0, Math.cos(angle) * apothem)
            mesh.rotation.y = angle
            mesh.userData.faceIndex = faceIndex

            this.yawGroup.add(mesh)
            this.faceMeshes.push(mesh)

            if (!caseData) {
                continue
            }

            this.clickableMeshes.push(mesh)

            if (caseData.imageUrl) {
                this.loadFaceTexture(mesh, caseData.imageUrl)
            }
        }

        const capDirections = [1, -1]

        // CircleGeometry vertices sit at multiples of 60°, which line up with the side-face edges
        capDirections.forEach((direction) => {
            const cap = new Mesh(capGeometry, capMaterial)
            cap.rotation.x = -direction * Math.PI / 2
            cap.position.y = direction * faceHeight / 2
            this.yawGroup.add(cap)
        })

        this.loadTopBrandMark(faceHeight)
    }

    /**
     * Transparent brand mark floating just above the purple top cap
     * @param {number} faceHeight
     */
    loadTopBrandMark(faceHeight) {
        const url = this.$section.getAttribute(this.BRAND_MARK_ATTRIBUTE)

        if (!url) {
            return
        }

        this.loadBrandMarkTexture(url)
            .then((texture) => {
                const size = this.FACE_WIDTH * 2 * this.BRAND_MARK_SCALE
                const mark = new Mesh(
                    new PlaneGeometry(size, size),
                    new MeshBasicMaterial({
                        map: texture,
                        transparent: true,
                        depthWrite: false
                    })
                )

                mark.rotation.x = -Math.PI / 2
                mark.position.y = faceHeight / 2 + this.BRAND_MARK_LIFT
                this.yawGroup.add(mark)
            })
            .catch((error) => console.log('Error loading section-3dfolio brand mark:', error))
    }

    /**
     * SVG textures need an explicit pixel size; browsers otherwise rasterize them tiny
     * @param {string} url
     * @returns {Promise<CanvasTexture>}
     */
    loadBrandMarkTexture(url) {
        const size = this.BRAND_MARK_TEXTURE_SIZE

        return fetch(url)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Brand mark fetch failed: ${response.status}`)
                }

                return response.text()
            })
            .then((svgText) => new Promise((resolve, reject) => {
                const sizedSvg = svgText.replace(
                    /<svg\b([^>]*)>/i,
                    (match, attributes) => {
                        const withoutSize = attributes
                            .replace(/\s(width|height)="[^"]*"/gi, '')
                            .trim()

                        return `<svg width="${size}" height="${size}" ${withoutSize}>`
                    }
                )
                const objectUrl = URL.createObjectURL(new Blob([sizedSvg], {type: 'image/svg+xml'}))
                const image = new Image()

                image.onload = () => {
                    const canvas = document.createElement('canvas')
                    canvas.width = size
                    canvas.height = size
                    canvas.getContext('2d').drawImage(image, 0, 0, size, size)
                    URL.revokeObjectURL(objectUrl)

                    const texture = new CanvasTexture(canvas)
                    texture.colorSpace = SRGBColorSpace
                    texture.anisotropy = this.renderer.capabilities.getMaxAnisotropy()
                    resolve(texture)
                }
                image.onerror = () => {
                    URL.revokeObjectURL(objectUrl)
                    reject(new Error('Brand mark image decode failed'))
                }
                image.src = objectUrl
            }))
    }

    /**
     * @returns {Color}
     */
    getBrandColor() {
        const value = window.getComputedStyle(this.$section).getPropertyValue(this.BRAND_COLOR_PROPERTY).trim()

        return new Color(value || this.FALLBACK_BRAND_COLOR)
    }

    /**
     * Unlit material keeps case images true to their original colors
     * @param {Mesh} mesh
     * @param {string} url
     */
    loadFaceTexture(mesh, url) {
        this.textureLoader.load(url, (texture) => {
            texture.colorSpace = SRGBColorSpace
            texture.anisotropy = this.renderer.capabilities.getMaxAnisotropy()
            this.applyCoverFit(texture)
            mesh.material = new MeshBasicMaterial({map: texture})
        }, undefined, (error) => console.log('Error loading section-3dfolio texture:', error))
    }

    /**
     * Crop the texture like `object-fit: cover` on a 1:1 face
     * @param {Texture} texture
     */
    applyCoverFit(texture) {
        const width = texture.image.naturalWidth || texture.image.width
        const height = texture.image.naturalHeight || texture.image.height

        if (!width || !height) {
            return
        }

        const imageAspect = width / height

        if (imageAspect > this.FACE_ASPECT) {
            texture.repeat.set(this.FACE_ASPECT / imageAspect, 1)
        } else {
            texture.repeat.set(1, imageAspect / this.FACE_ASPECT)
        }

        texture.offset.set((1 - texture.repeat.x) / 2, (1 - texture.repeat.y) / 2)
    }

    bindEvents() {
        const $canvas = this.renderer.domElement

        $canvas.addEventListener('pointerdown', (event) => this.handlePointerDown(event))
        $canvas.addEventListener('pointermove', (event) => this.handlePointerMove(event))
        $canvas.addEventListener('pointerup', (event) => this.handlePointerUp(event))
        $canvas.addEventListener('pointercancel', (event) => this.handlePointerCancel(event))

        this.$stage.addEventListener('pointermove', (event) => this.handleStagePointerMove(event), {passive: true})
        this.$stage.addEventListener('pointerleave', () => this.handleStagePointerLeave())

        if (this.$faceNav) {
            this.$faceNav.addEventListener('focusin', (event) => this.handleFaceNavFocusIn(event))
            this.$faceNav.addEventListener('focusout', (event) => this.handleFaceNavFocusOut(event))
            this.$faceNav.addEventListener('click', (event) => this.handleFaceNavClick(event))
        }
    }

    observeSize() {
        this.resize()

        if ('ResizeObserver' in window) {
            new ResizeObserver(() => this.resize()).observe(this.$stage)
        } else {
            window.addEventListener('resize', () => this.resize(), {passive: true})
        }
    }

    /**
     * Only render while the stage is on screen
     */
    observeVisibility() {
        if (!('IntersectionObserver' in window)) {
            this.startLoop()
            return
        }

        new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                this.startLoop()
            } else {
                this.stopLoop()
            }
        }).observe(this.$stage)
    }

    resize() {
        const width = this.$stage.clientWidth
        const height = this.$stage.clientHeight

        if (!width || !height) {
            return
        }

        this.state.stageWidth = width
        this.state.stageHeight = height
        this.renderer.setSize(width, height, false)
        this.camera.aspect = width / height
        this.fitCamera(this.camera.aspect)
        this.camera.updateProjectionMatrix()
        this.popup.updateSize()
    }

    startLoop() {
        if (this.state.frameId !== null) {
            return
        }

        this.state.lastTime = performance.now()
        this.state.frameId = window.requestAnimationFrame((time) => this.tick(time))
    }

    stopLoop() {
        if (this.state.frameId === null) {
            return
        }

        window.cancelAnimationFrame(this.state.frameId)
        this.state.frameId = null
    }

    /**
     * @param {number} time
     */
    tick(time) {
        const deltaSeconds = Math.min(Math.max(time - this.state.lastTime, 0) / 1000, this.MAX_FRAME_SECONDS)
        this.state.lastTime = time

        this.updateYaw(deltaSeconds)
        this.updateTilt(deltaSeconds)
        this.applyRotation()
        this.renderer.render(this.scene, this.camera)

        if (this.popup.isOpen) {
            this.updatePopupPosition()
        }

        this.state.frameId = window.requestAnimationFrame((nextTime) => this.tick(nextTime))
    }

    /**
     * Priority: drag > focused face > inertia + idle
     * @param {number} deltaSeconds
     */
    updateYaw(deltaSeconds) {
        const {state} = this

        if (state.isDragging) {
            return
        }

        if (state.focusTargetYaw !== null) {
            state.yaw = MathUtils.damp(state.yaw, state.focusTargetYaw, this.FOCUS_DAMPING, deltaSeconds)
            return
        }

        if (state.velocity !== 0) {
            state.yaw += state.velocity * deltaSeconds
            state.velocity = MathUtils.damp(state.velocity, 0, this.INERTIA_DAMPING, deltaSeconds)

            if (Math.abs(state.velocity) < this.INERTIA_MIN_VELOCITY) {
                state.velocity = 0
            }
        }

        if (!this.options.prefersReducedMotion) {
            state.yaw += this.IDLE_SPEED * deltaSeconds
        }
    }

    /**
     * @param {number} deltaSeconds
     */
    updateTilt(deltaSeconds) {
        const {state} = this

        state.tiltX = MathUtils.damp(state.tiltX, state.tiltTargetX, this.TILT_DAMPING, deltaSeconds)
        state.tiltY = MathUtils.damp(state.tiltY, state.tiltTargetY, this.TILT_DAMPING, deltaSeconds)
    }

    applyRotation() {
        this.yawGroup.rotation.y = this.state.yaw
        this.tiltGroup.rotation.set(this.state.tiltX, this.state.tiltY, 0)
    }

    /**
     * Keep the dialog glued to the projected center of its face
     */
    updatePopupPosition() {
        const mesh = this.faceMeshes[this.popup.faceIndex]

        if (!mesh) {
            return
        }

        this.scene.updateMatrixWorld()
        mesh.getWorldPosition(this.anchor).project(this.camera)

        this.popup.setPosition(
            (this.anchor.x + 1) / 2 * this.state.stageWidth,
            (1 - this.anchor.y) / 2 * this.state.stageHeight
        )
    }

    /**
     * Nearest yaw that turns the given face towards the camera
     * @param {number} faceIndex
     * @returns {number}
     */
    getFrontYaw(faceIndex) {
        const baseYaw = -faceIndex * TAU / this.FACE_COUNT
        const turns = Math.round((this.state.yaw - baseYaw) / TAU)

        return baseYaw + turns * TAU
    }

    /**
     * @param {number} faceIndex
     */
    focusFace(faceIndex) {
        this.state.velocity = 0
        this.state.focusTargetYaw = this.getFrontYaw(faceIndex)

        if (this.options.prefersReducedMotion) {
            this.state.yaw = this.state.focusTargetYaw
        }
    }

    /**
     * @param {number} faceIndex
     */
    openFace(faceIndex) {
        const caseData = this.cases[faceIndex]

        if (!caseData) {
            return
        }

        this.focusFace(faceIndex)
        this.applyRotation()
        this.popup.open(caseData, faceIndex)
        this.updatePopupPosition()
    }

    /**
     * @param {number} faceIndex
     */
    toggleFace(faceIndex) {
        if (this.popup.isOpen && this.popup.faceIndex === faceIndex) {
            this.popup.close()
            return
        }

        this.openFace(faceIndex)
    }

    handlePopupClose() {
        this.state.focusTargetYaw = null
    }

    /**
     * @param {PointerEvent} event
     */
    handlePointerDown(event) {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return
        }

        this.state.velocity = 0
        this.state.pointer = {
            id: event.pointerId,
            startX: event.clientX,
            startY: event.clientY,
            startYaw: this.state.yaw,
            lastX: event.clientX,
            lastTime: event.timeStamp,
            hasMoved: false
        }

        this.renderer.domElement.setPointerCapture(event.pointerId)
    }

    /**
     * Drag yaw after the click threshold; popup open blocks drag but still swallows the click
     * @param {PointerEvent} event
     */
    handlePointerMove(event) {
        const {state} = this
        const {pointer} = state

        if (!pointer || pointer.id !== event.pointerId) {
            this.updateHoverCursor(event)
            return
        }

        const deltaX = event.clientX - pointer.startX
        const deltaY = event.clientY - pointer.startY

        if (!pointer.hasMoved && Math.hypot(deltaX, deltaY) > this.CLICK_THRESHOLD_PX) {
            pointer.hasMoved = true

            if (!this.popup.isOpen) {
                state.isDragging = true
                state.focusTargetYaw = null
                this.$stage.classList.add(this.GRABBING_CLASS)
            }
        }

        if (!state.isDragging) {
            return
        }

        const elapsedSeconds = Math.max(event.timeStamp - pointer.lastTime, 1) / 1000

        state.yaw = pointer.startYaw + deltaX * this.DRAG_SPEED
        state.velocity = (event.clientX - pointer.lastX) * this.DRAG_SPEED / elapsedSeconds
        pointer.lastX = event.clientX
        pointer.lastTime = event.timeStamp
    }

    /**
     * @param {PointerEvent} event
     */
    handlePointerUp(event) {
        const {state} = this
        const {pointer} = state

        if (!pointer || pointer.id !== event.pointerId) {
            return
        }

        if (!pointer.hasMoved) {
            this.handleTap(event)
        }

        // A pause before release means the user stopped; do not fling with a stale velocity
        if (this.options.prefersReducedMotion || event.timeStamp - pointer.lastTime > this.INERTIA_MAX_IDLE_MS) {
            state.velocity = 0
        }

        this.endPointer()
    }

    /**
     * @param {PointerEvent} event
     */
    handlePointerCancel(event) {
        if (!this.state.pointer || this.state.pointer.id !== event.pointerId) {
            return
        }

        this.state.velocity = 0
        this.endPointer()
    }

    endPointer() {
        this.state.pointer = null
        this.state.isDragging = false
        this.$stage.classList.remove(this.GRABBING_CLASS)
    }

    /**
     * @param {PointerEvent} event
     */
    handleTap(event) {
        const mesh = this.getIntersectedFace(event)

        if (mesh) {
            this.toggleFace(mesh.userData.faceIndex)
        }
    }

    /**
     * First prism hit decides, so caps and empty faces block faces behind them
     * @param {PointerEvent} event
     * @returns {Mesh|null}
     */
    getIntersectedFace(event) {
        const rect = this.renderer.domElement.getBoundingClientRect()

        if (!rect.width || !rect.height) {
            return null
        }

        this.pointerNdc.set(
            (event.clientX - rect.left) / rect.width * 2 - 1,
            -((event.clientY - rect.top) / rect.height) * 2 + 1
        )
        this.raycaster.setFromCamera(this.pointerNdc, this.camera)

        const [hit] = this.raycaster.intersectObjects(this.yawGroup.children, false)

        return hit && this.clickableMeshes.includes(hit.object) ? hit.object : null
    }

    /**
     * @param {PointerEvent} event
     */
    updateHoverCursor(event) {
        if (event.pointerType !== 'mouse') {
            return
        }

        this.$stage.classList.toggle(this.CLICKABLE_CLASS, this.getIntersectedFace(event) !== null)
    }

    /**
     * Mouse-only parallax; touch and reduced motion keep the prism level
     * @param {PointerEvent} event
     */
    handleStagePointerMove(event) {
        if (event.pointerType !== 'mouse' || this.options.prefersReducedMotion || !this.options.supportsHover) {
            return
        }

        const rect = this.$stage.getBoundingClientRect()
        const maxTilt = MathUtils.degToRad(this.TILT_MAX_DEG)

        if (!rect.width || !rect.height) {
            return
        }

        // Lean away from the cursor (parallax), not toward it
        this.state.tiltTargetY = -((event.clientX - rect.left) / rect.width * 2 - 1) * maxTilt
        this.state.tiltTargetX = ((event.clientY - rect.top) / rect.height * 2 - 1) * maxTilt
    }

    handleStagePointerLeave() {
        this.state.tiltTargetX = 0
        this.state.tiltTargetY = 0
        this.$stage.classList.remove(this.CLICKABLE_CLASS)
    }

    /**
     * Keyboard users see which face a button belongs to before opening it
     * @param {FocusEvent} event
     */
    handleFaceNavFocusIn(event) {
        const faceIndex = this.getFaceButtonIndex(event.target)

        if (faceIndex !== null && !this.popup.isOpen) {
            this.focusFace(faceIndex)
        }
    }

    /**
     * @param {FocusEvent} event
     */
    handleFaceNavFocusOut(event) {
        if (!this.popup.isOpen && !this.$faceNav.contains(event.relatedTarget)) {
            this.state.focusTargetYaw = null
        }
    }

    /**
     * @param {MouseEvent} event
     */
    handleFaceNavClick(event) {
        const faceIndex = this.getFaceButtonIndex(event.target)

        if (faceIndex !== null) {
            this.toggleFace(faceIndex)
        }
    }

    /**
     * @param {EventTarget|null} $target
     * @returns {number|null}
     */
    getFaceButtonIndex($target) {
        const $button = $target instanceof Element ? $target.closest(this.FACE_BUTTON_SELECTOR) : null

        if (!$button) {
            return null
        }

        const faceIndex = parseInt($button.getAttribute(this.FACE_BUTTON_ATTRIBUTE), 10)

        return Number.isNaN(faceIndex) ? null : faceIndex
    }
}
