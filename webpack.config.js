require("webpack")
const path = require("path")
const glob = require("glob")

//check env variables
const production = process.env.NODE_ENV === 'production'
const isLiveReload = process.env?.LIVE_RELOAD && process.env.LIVE_RELOAD === 'true'

//import plugins
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const SpritePlugin = require("svg-sprite-loader/plugin")
const IgnoreEmitPlugin = require("ignore-emit-webpack-plugin")
const TerserPlugin = require("terser-webpack-plugin")
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin")
const RemoveEmptyScriptsPlugin = require("webpack-remove-empty-scripts")
const {WebpackManifestPlugin} = require("webpack-manifest-plugin")
const {CleanWebpackPlugin} = require("clean-webpack-plugin")
const LiveReloadPlugin = require("webpack-livereload-plugin")

// svg-sprite-loader writes `_value` then clears `_valueAsString`, which
// webpack-sources 3 needs for RawSource.size() during production stats.
class RestoreSvgSpriteSourceSizePlugin {
    apply(compiler) {
        compiler.hooks.thisCompilation.tap('RestoreSvgSpriteSourceSizePlugin', (compilation) => {
            compilation.hooks.afterOptimizeChunks.tap('RestoreSvgSpriteSourceSizePlugin', () => {
                for (const module of compilation.modules) {
                    const source = module._source
                    if (
                        source &&
                        typeof source._value === 'string' &&
                        source._valueAsString === undefined
                    ) {
                        source._valueAsString = source._value
                        source._cachedSize = undefined
                    }
                }
            })
        })
    }
}

//project setup
const THEME_NAME = 'technosapiens'
const THEME_DIRECTORY = 'technosapiens'
const themeDirectory = path.join(__dirname, `public/wp-content/themes/${THEME_DIRECTORY}/`)
const distDirectory = path.join(__dirname, `public/wp-content/themes/${THEME_DIRECTORY}/dist/`)
const icons = glob.sync(path.join(themeDirectory, `icons/*.svg`))

const collectAssetEntries = (basePaths, entryPrefix = '') => {
    const entries = {}

    if (!basePaths || basePaths.length === 0) {
        return entries
    }

    basePaths.forEach((basePath) => {
        const stylesheets = glob.sync(path.join(basePath, `scss/*.scss`))
        const scripts = glob.sync(path.join(basePath, `js/*.js`))

        stylesheets.forEach((scssPath) => {
            const entryName = scssPath.substring(scssPath.lastIndexOf('/') + 1).replace('.scss', '')
            if (entryName) entries[`${entryPrefix}${entryName}-style`] = [scssPath]
        })

        scripts.forEach((scriptPath) => {
            const entryName = scriptPath.substring(scriptPath.lastIndexOf('/') + 1).replace('.js', '')
            if (entryName) entries[`${entryPrefix}${entryName}-script`] = [scriptPath]
        })
    })

    return entries
}

//scan for block assets which should be ran through webpack
const blockPaths = glob.sync(path.join(themeDirectory, `blocks/*`))
const blockEntries = collectAssetEntries(blockPaths)

//scan for section assets which should be ran through webpack
const sectionPaths = glob.sync(path.join(themeDirectory, `sections/*`))
    .concat(glob.sync(path.join(themeDirectory, `sections/reference/*`)))
const sectionEntries = collectAssetEntries(sectionPaths)

//scan for post types assets which should be ran through webpack
const postTypeRoot = path.join(themeDirectory, 'post-types')
const postTypePaths = glob.sync(path.join(postTypeRoot, '*'))
const postTypeEntries = {}
if (postTypePaths && postTypePaths.length > 0) {
    postTypePaths.forEach((postTypePath) => {
        const postTypeSlug = path.relative(postTypeRoot, postTypePath).split(path.sep).join('-')

        //scan post types files
        const stylesheets = glob.sync(path.join(postTypePath, 'scss/**/*.scss'))
        const scripts = glob.sync(path.join(postTypePath, `js/*.js`))

        stylesheets.forEach((scssPath) => {
            const entryName = scssPath.substring(scssPath.lastIndexOf('/') + 1).replace('.scss', '')
            if (entryName) postTypeEntries[`${postTypeSlug}-${entryName}-style`] = [scssPath]
        })

        scripts.forEach((scriptPath) => {
            const entryName = scriptPath.substring(scriptPath.lastIndexOf('/') + 1).replace('.js', '')
            if (entryName) postTypeEntries[`${postTypeSlug}-${entryName}-script`] = [scriptPath]
        })
    })
}

//scan for mega menu preset assets which should be ran through webpack
const megaMenuPresetPaths = glob.sync(path.join(themeDirectory, `inc/features/mega-menu/presets/*`))
const megaMenuPresetEntries = {}
if (megaMenuPresetPaths && megaMenuPresetPaths.length > 0) {
    megaMenuPresetPaths.forEach((megaMenuPresetPath) => {
        //scan block files
        const stylesheets = glob.sync(path.join(megaMenuPresetPath, `scss/*.scss`))
        const scripts = glob.sync(path.join(megaMenuPresetPath, `js/*.js`))

        stylesheets.forEach((scssPath) => {
            const entryName = scssPath.substring(scssPath.lastIndexOf('/') + 1).replace('.scss', '')
            if (entryName) megaMenuPresetEntries[`${entryName}-style`] = [scssPath]
        })

        scripts.forEach((scriptPath) => {
            const entryName = scriptPath.substring(scriptPath.lastIndexOf('/') + 1).replace('.js', '')
            if (entryName) megaMenuPresetEntries[`${entryName}-script`] = [scriptPath]
        })
    })
}

//scan for dynamic SCSS chunks which should be ran through webpack and output individual files
const scssPaths = glob.sync(path.join(themeDirectory, `scss/chunk/**/*.scss`))
const scssEntries = {}
if (scssPaths && scssPaths.length > 0) {
    scssPaths.forEach((scssPath) => {
        const entryName = scssPath.substring(scssPath.lastIndexOf('/') + 1).replace('.scss', '')
        if (entryName) scssEntries[entryName] = [scssPath]
    })
}

//scan for dynamic JS chunks which should be ran through webpack and output individual files
const jsChunkPaths = glob.sync(path.join(themeDirectory, `js/chunk/*/*.js`))
const jsChunkEntries = {}
if (jsChunkPaths && jsChunkPaths.length > 0) {
    jsChunkPaths.forEach((jsPath) => {
        const entryName = jsPath.substring(jsPath.lastIndexOf('/') + 1).replace('.js', '')
        if (entryName) jsChunkEntries[`${entryName}-script`] = [jsPath]
    })
}

module.exports = {

    target: production ? ['web', 'es5'] : 'web',

    entry: {
        Dialog: path.join(themeDirectory, `js/lib/Dialog.js`),
        initDialogs: path.join(themeDirectory, `js/initDialogs.js`),
        frontend: [
            path.join(themeDirectory, `scss/${THEME_NAME}-frontend.scss`),
            path.join(themeDirectory, `js/${THEME_NAME}-frontend.js`)
        ],
        admin: [
            path.join(themeDirectory, `scss/${THEME_NAME}-admin.scss`),
            path.join(themeDirectory, `js/${THEME_NAME}-admin.js`)
        ],
        login: [
            path.join(themeDirectory, `scss/${THEME_NAME}-login.scss`),
        ],
        gutenberg: [
            path.join(themeDirectory, `scss/${THEME_NAME}-gutenberg.scss`),
        ],
        theme: [path.join(themeDirectory, `scss/${THEME_NAME}-admin-theme.scss`)],
        ...(isLiveReload && !production) ? {livereload: path.join(themeDirectory, `js/utils/live-reload.js`)} : {},
        ...icons.length > 0 ? {icons} : {},
        ...scssEntries,
        ...jsChunkEntries,
        ...blockEntries,
        ...sectionEntries,
        ...postTypeEntries,
        ...megaMenuPresetEntries
    },

    ...production ? {} : {devtool: 'inline-source-map'},

    output: {
        filename: `${THEME_NAME}-[name]${production ? '-[contenthash]' : ''}.min.js`,
        chunkFilename: `${THEME_NAME}-chunk-[name]${production ? '-[contenthash]' : ''}.min.js`,
        path: distDirectory,
        publicPath: `/wp-content/themes/${THEME_NAME}/dist/`
    },

    module: {
        rules: [
            {
                test: /\.js?$/,
                exclude: /node_modules/,
                use: 'babel-loader'
            },
            {
                test: /\.(ttf|eot|woff|woff2|svg)$/,
                use: 'file-loader?name=fonts/[name].[ext]',
                exclude: [
                    /node_modules/,
                    path.join(themeDirectory, 'images'),
                    path.join(themeDirectory, 'icons'),
                ]
            },
            {
                test: /\.svg$/,
                loader: 'svg-sprite-loader',
                options: {
                    extract: true,
                    spriteFilename: `${THEME_NAME}-icons${production ? '-[contenthash]' : ''}.svg`,
                },
                exclude: [
                    /node_modules/
                ],
                include: [
                    path.join(themeDirectory, 'images'),
                    path.join(themeDirectory + 'icons')
                ]
            },
            {
                test: /\.scss|css$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: {
                            publicPath: '/'
                        }
                    },
                    {
                        loader: 'css-loader',
                        options: {
                            url: {
                                filter: url => !url.startsWith('/')
                            }
                        }
                    },
                    'postcss-loader',
                    'sass-loader'
                ]
            },
            {
                test: /\.(gif|jpe?g|png)$/,
                exclude: [/node_modules/, /fonts/],
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: '[name].[ext]',
                            publicPath: '/'
                        }
                    }
                ]
            }
        ]
    },

    optimization: {
        nodeEnv: production ? 'production' : 'development',
        minimize: production,
        ...production ? {
            mangleExports: 'size',
            minimizer: [
                new TerserPlugin({
                    test: /\.js?$/,
                    extractComments: false
                }),
                new CssMinimizerPlugin({
                    test: /\.scss|css$/,
                    minimizerOptions: {
                        preset: require.resolve('cssnano-preset-default'),
                    },
                })
            ]
        } : {},
    },

    performance: {
        hints: false,
    },

    plugins: [
        //prevent output of empty javascript file for SCSS entries
        new RemoveEmptyScriptsPlugin({extensions: /\.(scss|css)$/}),

        //extract all css per entry to one distiled file
        new MiniCssExtractPlugin({
            filename: `${THEME_NAME}-[name]${production ? '-[contenthash]' : ''}.min.css`,
            chunkFilename: "[name].css"
        }),

        //extract SVG icons to one sprite
        new SpritePlugin({plainSprite: true}),
        new RestoreSvgSpriteSourceSizePlugin(),

        //prevent output of javascript file for icons
        new IgnoreEmitPlugin([/(.+)-icons-(.+).js/]),

        //add JSON manifest for loading files in PHP with a dynamic hash in the name
        new WebpackManifestPlugin({}),

        //add live reload plugin for development
        ...(isLiveReload && !production) ? [new LiveReloadPlugin({useSourceHash: true})] : [],

        // clear dist folder before building
        new CleanWebpackPlugin(),
    ]
}