<?php

namespace MediaWiki\Hook;

/**
 * @stable for implementation
 * @ingroup Hooks
 */
interface IsTrustedProxyHook {
	/**
	 * Use this hook to override the result of ProxyLookup::isTrustedProxy().
	 *
	 * @since 1.35
	 *
	 * @param string $ip IP being checked
	 * @param array &$result Change this value to override the result of ProxyLookup::isTrustedProxy()
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onIsTrustedProxy( $ip, &$result );
}
