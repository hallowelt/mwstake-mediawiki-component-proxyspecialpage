<?php

namespace MWStake\MediaWiki\Component\ProxySpecialPage\Special;

use MediaWiki\Title\TitleFactory;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;

abstract class DashboardProxyBase extends \SpecialPage {

	protected TitleFactory $titleFactory;

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
	}

	/**
	 * @param string|null $subPage
	 * @return void
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );
		$this->getOutput()->enableOOUI();
		$inclusionTargetName = $this->getInclusionTargetName();
		$userName = $this->getUser()->getName();
		$userOverrideInclusionTargetName = "$userName/$inclusionTargetName";
		$userOverrideInclusionTarget = $this->titleFactory->makeTitle(
			NS_USER,
			$userOverrideInclusionTargetName
		);
		// TODO: Permission check if user can edit $inclusionTargetName
		// TODO: Add $inclusionTargetName as `preload` parameter if $userOverrideInclusionTarget does not exist yet
		if ( $userOverrideInclusionTarget->exists() ) {
			$inclusionTargetName = $userOverrideInclusionTarget->getPrefixedDBkey();
			$customizeInfoMsg = $this->msg(
				'bs-galaxy-dashboard-overridden-info',
				$userOverrideInclusionTargetName
			);
		} else {
			$customizeInfoMsg = $this->msg(
				'bs-galaxy-dashboard-override-link',
				$userOverrideInclusionTargetName,
				$inclusionTargetName
			);
		}
		$customizeInfo = new MessageWidget( [
			'label' => new HtmlSnippet( $customizeInfoMsg->parse() )
		] );
		$this->getOutput()->addHTML( $customizeInfo );

		$this->getOutput()->addWikiTextAsInterface( '{{' . $inclusionTargetName . '}}' );
	}

	/**
	 * @return string
	 */
	abstract protected function getInclusionTargetName(): string;

}
