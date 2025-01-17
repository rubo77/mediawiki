<?php

namespace MediaWiki\Search\Hook;

use Title;

/**
 * @stable for implementation
 * @ingroup Hooks
 */
interface SearchGetNearMatchBeforeHook {
	/**
	 * Use this hook to perform exact-title-matches in "go" searches before
	 * the normal operations.
	 *
	 * @since 1.35
	 *
	 * @param array $allSearchTerms Array of the search terms in all content languages
	 * @param Title|null &$titleResult Outparam; the value to return
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSearchGetNearMatchBefore( $allSearchTerms, &$titleResult );
}
