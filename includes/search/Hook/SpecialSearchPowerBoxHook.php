<?php

namespace MediaWiki\Search\Hook;

/**
 * @stable for implementation
 * @ingroup Hooks
 */
interface SpecialSearchPowerBoxHook {
	/**
	 * Use this hook to modify advanced search profile forms.
	 * This hook is equivalent to SpecialSearchProfileForm for
	 * the advanced form, also know as power search box.
	 *
	 * @since 1.35
	 *
	 * @param array &$showSections Array to add values with more options to
	 * @param string $term Search term (not a title object)
	 * @param array &$opts Array of hidden options (containing 'redirs' and 'profile')
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSpecialSearchPowerBox( &$showSections, $term, &$opts );
}
