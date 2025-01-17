<?php

namespace MediaWiki\Search\Hook;

use SearchResult;
use SpecialSearch;
use Title;

/**
 * @stable for implementation
 * @ingroup Hooks
 */
interface ShowSearchHitTitleHook {
	/**
	 * Use this hook to customise display of search hit title/link.
	 *
	 * @since 1.35
	 *
	 * @param Title &$title Title to link to
	 * @param string &$titleSnippet Label for the link representing the search result. Typically the
	 *   article title.
	 * @param SearchResult $result
	 * @param array $terms Array of search terms extracted by SearchDatabase search engines
	 *   (may not be populated by other search engines)
	 * @param SpecialSearch $specialSearch
	 * @param array &$query Array of query string parameters for the link representing the search
	 *   result
	 * @param array &$attributes Array of title link attributes, can be modified by extension
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onShowSearchHitTitle( &$title, &$titleSnippet, $result, $terms,
		$specialSearch, &$query, &$attributes
	);
}
