<?php

namespace MediaWiki\Languages\Hook;

/**
 * @stable for implementation
 * @ingroup Hooks
 */
interface LanguageGetTranslatedLanguageNamesHook {
	/**
	 * Use this hook to provide translated language names.
	 *
	 * @since 1.35
	 *
	 * @param array &$names Array of language code => language name
	 * @param string $code Language of the preferred translations
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onLanguageGetTranslatedLanguageNames( &$names, $code );
}
