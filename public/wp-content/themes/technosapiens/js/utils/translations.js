/**
 * Translations
 *
 * @param   {String} namespace The global namespace (property name)
 * @return  {Object}
 */
const translations = (namespace) => {

    // set translations
    const translations = namespace in window ? window[namespace] : false
    if (!translations) {
        throw new Error(`Can not find translation object [${namespace}] in window`)
    }

    return {
        /**
         * Get translation by id
         *
         * @param {String} id
         * @param {String|null} def
         * @return {String|boolean.<false>}
         */
        get(id, def = null) {
            return translations[id] || def || false
        }
    }
}

export default translations

const getTranslationsByNamespace = (namespace) => translations(namespace)

export {
    getTranslationsByNamespace
}