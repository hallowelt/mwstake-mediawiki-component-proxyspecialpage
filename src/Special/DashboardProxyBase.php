<?php

namespace MWStake\MediaWiki\Component\ProxySpecialPage\Special;

use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;

abstract class DashboardProxyBase extends \SpecialPage {

	protected TitleFactory $titleFactory;

	protected PermissionManager $permissionManager;

	/**
	 * @inheritDoc
	 */
	public function __construct( $name = '',
		$restriction = '',
		$listed = true,
		$function = false,
		$file = '',
		$includable = false ) {
		parent::__construct( $name, $restriction, $listed, $function, $file, $includable );
		$this->titleFactory = \MediaWiki\MediaWikiServices::getInstance()->getTitleFactory();
		$this->permissionManager = \MediaWiki\MediaWikiServices::getInstance()->getPermissionManager();
	}

	/**
	 * @param string|null $subPage
	 * @return void
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );
		$this->getOutput()->enableOOUI();
		$inclusionTargetName = $this->getInclusionTargetName();

		$this->getOutput()->addWikiTextAsInterface( '{{' . $inclusionTargetName . '}}' );

		$notice = $this->getNotice();
		if ( $notice ) {
			$customizeInfo = new MessageWidget( [
				'label' => new HtmlSnippet( $notice->parse() ),
				'inline' => true,
				'classes' => [ 'mwstake-component-proxyspecialpage-dashboard-info-cnt' ]
			] );
			$this->getOutput()->addHTML( $customizeInfo );
		}
	}

	/**
	 * @return Message|null
	 */
	protected function getNotice() {
		$user = $this->getUser();
		$userName = $user->getName();
		$inclusionTargetName = $this->getInclusionTargetName();
		$userOverrideInclusionTargetName = "$userName/$inclusionTargetName";
		$userOverrideInclusionTarget = $this->titleFactory->makeTitle(
			NS_USER,
			$userOverrideInclusionTargetName
		);

		$inclusionTitle = $this->titleFactory->newFromText( $inclusionTargetName );
		$canEditForAll = $this->permissionManager->userCan( 'edit', $user, $inclusionTitle );
		$canEditUser = $this->permissionManager->userCan( 'edit', $user, $userOverrideInclusionTarget );
		if ( !$canEditForAll && !$canEditUser ) {
			return null;
		}

		// TODO: Add $inclusionTargetName as `preload` parameter if $userOverrideInclusionTarget does not exist yet
		if ( $userOverrideInclusionTarget->exists() ) {
			if ( !$canEditUser ) {
				return null;
			}
			$inclusionTargetName = $userOverrideInclusionTarget->getPrefixedDBkey();
			return $this->msg(
				'mwstake-component-proxyspecialpage-dashboard-overridden-info',
				$userOverrideInclusionTargetName
			);
		} else {
			if ( !$canEditForAll && $canEditUser ) {
				return $this->msg(
					'mwstake-component-proxyspecialpage-user-override-link',
					$userOverrideInclusionTargetName . '?preload=' . $inclusionTargetName
				);
			}

			if ( $canEditForAll && $canEditUser ) {
				return $this->msg(
					'mwstake-component-proxyspecialpage-dashboard-override-link',
					$userOverrideInclusionTargetName,
					$inclusionTargetName
				);
			}
		}
		return null;
	}

	/**
	 * @return string
	 */
	abstract protected function getInclusionTargetName(): string;

}
