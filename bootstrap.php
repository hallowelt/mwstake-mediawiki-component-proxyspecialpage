<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_PROXYSPECIALPAGE_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_PROXYSPECIALPAGE_VERSION', '2.0.0' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'proxyspecialpage', static function () {
	$GLOBALS['wgMessagesDirs']['mwstake-component-proxyspecialpage'] = __DIR__ . '/i18n';
} );
