<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup RevisionDelete
 */

use MediaWiki\Revision\RevisionRecord;

/**
 * Item class for a live revision table row
 *
 * @property RevDelRevisionList $list
 */
class RevDelRevisionItem extends RevDelItem {
	/** @var Revision */
	public $revision;

	public function __construct( RevisionListBase $list, $row ) {
		parent::__construct( $list, $row );
		$this->revision = static::initRevision( $list, $row );
	}

	/**
	 * Create revision object from $row sourced from $list
	 *
	 * @param RevisionListBase $list
	 * @param mixed $row
	 * @return Revision
	 */
	protected static function initRevision( $list, $row ) {
		return new Revision( $row );
	}

	public function getIdField() {
		return 'rev_id';
	}

	public function getTimestampField() {
		return 'rev_timestamp';
	}

	public function getAuthorIdField() {
		return 'rev_user';
	}

	public function getAuthorNameField() {
		return 'rev_user_text';
	}

	public function getAuthorActorField() {
		return 'rev_actor';
	}

	public function canView() {
		return RevisionRecord::userCanBitfield(
			$this->revision->getVisibility(),
			RevisionRecord::DELETED_RESTRICTED,
			$this->list->getUser()
		);
	}

	public function canViewContent() {
		return RevisionRecord::userCanBitfield(
			$this->revision->getVisibility(),
			RevisionRecord::DELETED_TEXT,
			$this->list->getUser()
		);
	}

	public function getBits() {
		return $this->revision->getVisibility();
	}

	public function setBits( $bits ) {
		$dbw = wfGetDB( DB_MASTER );
		// Update revision table
		$dbw->update( 'revision',
			[ 'rev_deleted' => $bits ],
			[
				'rev_id' => $this->revision->getId(),
				'rev_page' => $this->revision->getPage(),
				'rev_deleted' => $this->getBits() // cas
			],
			__METHOD__
		);
		if ( !$dbw->affectedRows() ) {
			// Concurrent fail!
			return false;
		}
		// Update recentchanges table
		$dbw->update( 'recentchanges',
			[
				'rc_deleted' => $bits,
				'rc_patrolled' => RecentChange::PRC_AUTOPATROLLED
			],
			[
				'rc_this_oldid' => $this->revision->getId(), // condition
			],
			__METHOD__
		);

		return true;
	}

	public function isDeleted() {
		return $this->revision->isDeleted( RevisionRecord::DELETED_TEXT );
	}

	public function isHideCurrentOp( $newBits ) {
		return ( $newBits & RevisionRecord::DELETED_TEXT )
			&& $this->list->getCurrent() == $this->getId();
	}

	/**
	 * Get the HTML link to the revision text.
	 * Overridden by RevDelArchiveItem.
	 * @return string
	 */
	protected function getRevisionLink() {
		$date = $this->list->getLanguage()->userTimeAndDate(
			$this->revision->getTimestamp(), $this->list->getUser() );

		if ( $this->isDeleted() && !$this->canViewContent() ) {
			return htmlspecialchars( $date );
		}

		return $this->getLinkRenderer()->makeKnownLink(
			$this->list->title,
			$date,
			[],
			[
				'oldid' => $this->revision->getId(),
				'unhide' => 1
			]
		);
	}

	/**
	 * Get the HTML link to the diff.
	 * Overridden by RevDelArchiveItem
	 * @return string
	 */
	protected function getDiffLink() {
		if ( $this->isDeleted() && !$this->canViewContent() ) {
			return $this->list->msg( 'diff' )->escaped();
		} else {
			return $this->getLinkRenderer()->makeKnownLink(
					$this->list->title,
					$this->list->msg( 'diff' )->text(),
					[],
					[
						'diff' => $this->revision->getId(),
						'oldid' => 'prev',
						'unhide' => 1
					]
				);
		}
	}

	/**
	 * @return string A HTML <li> element representing this revision, showing
	 * change tags and everything
	 */
	public function getHTML() {
		$difflink = $this->list->msg( 'parentheses' )
			->rawParams( $this->getDiffLink() )->escaped();
		$revlink = $this->getRevisionLink();
		$userlink = Linker::revUserLink( $this->revision->getRevisionRecord() );
		$comment = Linker::revComment( $this->revision->getRevisionRecord() );
		if ( $this->isDeleted() ) {
			$revlink = "<span class=\"history-deleted\">$revlink</span>";
		}
		$content = "$difflink $revlink $userlink $comment";
		$attribs = [];
		$tags = $this->getTags();
		if ( $tags ) {
			list( $tagSummary, $classes ) = ChangeTags::formatSummaryRow(
				$tags,
				'revisiondelete',
				$this->list->getContext()
			);
			$content .= " $tagSummary";
			$attribs['class'] = implode( ' ', $classes );
		}
		return Xml::tags( 'li', $attribs, $content );
	}

	/**
	 * @return string Comma-separated list of tags
	 */
	public function getTags() {
		return $this->row->ts_tags;
	}

	public function getApiData( ApiResult $result ) {
		$rev = $this->revision;
		$user = $this->list->getUser();
		$ret = [
			'id' => $rev->getId(),
			'timestamp' => wfTimestamp( TS_ISO_8601, $rev->getTimestamp() ),
			'userhidden' => (bool)$rev->isDeleted( RevisionRecord::DELETED_USER ),
			'commenthidden' => (bool)$rev->isDeleted( RevisionRecord::DELETED_COMMENT ),
			'texthidden' => (bool)$rev->isDeleted( RevisionRecord::DELETED_TEXT ),
		];
		if ( RevisionRecord::userCanBitfield(
			$rev->getVisibility(),
			RevisionRecord::DELETED_USER,
			$user
		) ) {
			$ret += [
				'userid' => $rev->getUser( RevisionRecord::FOR_THIS_USER, $user ),
				'user' => $rev->getUserText( RevisionRecord::FOR_THIS_USER, $user ),
			];
		}
		if ( RevisionRecord::userCanBitfield(
			$rev->getVisibility(),
			RevisionRecord::DELETED_COMMENT,
			$user
		) ) {
			$ret += [
				'comment' => $rev->getComment( RevisionRecord::FOR_THIS_USER, $user ),
			];
		}

		return $ret;
	}
}
