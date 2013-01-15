<?php
defined('_JEXEC') or die('Restricted Access'); 
jimport('joomla.application.component.view');

class GalleryViewGallery extends JView
{
	function display($tpl = null)
	{
		// params
		$params = JFactory::getApplication()->getParams();
		$galleryPath = $params->get('gallery_path');
		
		// validate
		$folderPath = JFolder::makeSafe(JRequest::getString('path'));
		
		// get path from GET
		$folder = new Folder($galleryPath, $folderPath);
		
		// get child folders of this folder
		$childFolders = $folder->getChildFolders(false);
		
		// remove empty folders from list
		for ($i = 0; $i < $childFolders->count(); $i++) {
			if (!$childFolders[$i]->hasPhotos(true)) {
				$childFolders->offsetUnset($i);
			}
		}

		// get photos of this folder
		$photos = $folder->getPhotos();

		// add scripts
		$document = &JFactory::getDocument();
		
		$document->addScript('media/com_gallery/js/jquery-1.8.3.min.js');
		$document->addScript('media/com_gallery/js/shutter-reloaded.js');
		$document->addScript('media/com_gallery/js/jquery.capty.min.js');
		
		$shutterImagesPath = JURI::root(true) . DS . 'media' . DS . 'com_gallery' . DS . 'images' . DS . 'shutter' . DS;
		$document->addScriptDeclaration('
			$(function() {
				shutterReloaded.init(0, \''. $shutterImagesPath . '\');
			});
		');
		
		$document->addScriptDeclaration('
			$(function(){
				$(\'#gallery .caption\').capty({
					animation: \'fade\',
					speed: 400
				});
			});
		');
		// ---
		
		// add css
		$document->addStyleSheet('media/com_gallery/css/gallery.style.css');
		$document->addStyleSheet('media/com_gallery/css/jquery.capty.css');
		$document->addStyleSheet('media/com_gallery/css/shutter-reloaded.css');
		
		// get title
		if ($folderPath == '') {
			$title = 'Gallery';
		} else {
			$title = $folder->getFolderName();
		}
		
		// set breadcrumbs
		$pathway = JSite::getPathway();
				
		foreach ($folder->getFolderNames() as $folderName) {
			
			if ($folderName == '') {
				continue; // skip if foldername is empty
			}
			
			if (isset($currentPath)) {
				$currentPath .= DS . $folderName;
			} else {
				$currentPath = $folderName;
			}
			
			// replace underscores
			$tmpFolder = new Folder($galleryPath, $folderName); // TODO use a static methode to remove object overhead
			$folderName = $tmpFolder->getReadableFolderName();
			
			$pathway->addItem($folderName, 'index.php?option=com_gallery&path=' . $currentPath);
		}
		
		// set title
		$document = JFactory::getDocument();
		$folderName = $folder->getReadableFolderName();
		$app = JFactory::getApplication();
		
		if ($folderName == '') {
			$folderName = $document->getTitle();
		}
		
		$sitename = $app->getCfg('sitename', ''); // TODO validate ?
		switch ($app->getCfg('sitename_pagetitles', 0)) {
			case 2: // after
				$document->setTitle($folderName . ' - ' . $sitename);
				break;
			case 1: // before
				$document->setTitle($sitename . ' - ' . $folderName);
				break;
			default: // none
				$document->setTitle($folderName);
		}
		// ---
		
		// assign Variables
		$this->assignRef('title', $title);
		$this->assignRef('childFolders', $childFolders);
		$this->assignRef('photos', $photos);
		
		parent::display($tpl);
	}
}
?>