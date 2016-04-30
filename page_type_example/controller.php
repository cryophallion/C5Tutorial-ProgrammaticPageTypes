<?php
namespace Concrete\Package\PageTypeExample;

use Package;
use Page;
use PageType;
use PageTemplate;
use \Concrete\Core\Page\Type\PublishTarget\Type\Type as PublishTargetType;

class Controller extends Package
{
    protected $pkgHandle = 'page_type_example';
    protected $appVersionRequired = '5.7.5.2';
    protected $pkgVersion = '0.0.1';
    protected $previousVersion = '0.0.0';

    public function getPackageDescription()
    {
        return t('Concrete 5.7+ Programmatic Page Types example code');
    }

    public function getPackageName()
    {
        return t('Programmatic Page Types Example');
    }

    public function install()
    {
        $pkg = parent::install();
        $this->installOrUpgrade($pkg);
    }

    public function upgrade()
    {
        $pkg = Package::getByHandle($this->pkgHandle);
        $this->previousVersion = $pkg->getPackageVersion();
        parent::upgrade();
        $this->installOrUpgrade($pkg);
    }

    protected function installOrUpgrade($pkg)
    {
        
        //Parent Page Example:
        
        //Create "News" page for installing the Page Type under. Just set to a full page 
        $newsPage = $this->addPage('ppt-news', 'PPT News', 'Programmatic Page Types News', 'page', 'full', 1, $pkg);
        
        //Create News Items Page Type that can be only under News Page
        $newsItemPageType = $this->addPageTypeWithParentPagePublishTarget('ppt_news_item', 'PPT News Item', 'left_sidebar', 'C', array('left_sidebar'), $newsPage->getCollectionID(), $pkg);
        
        //Page Type Example
        
        //Let's say that there were subpages on News Item Pages for any files, or things of that nature. Let's allow a NewsItemFile Page Type.
        //For demonstration purposes, we will also restrict it to having to be under the News Page as well.
        
        $newsItemFilePageType = $this->addPageTypeWithPageTypePublishTarget('ppt_news_item_file', 'PPT News Item File', 'left_sidebar', 'C', array('left_sidebar'), $newsItemPageType->getPageTypeID(), $pkg, $newsPage->getCollectionID());
        
    }
    
    /**
     * Add a Specific Page
     * @param string|int $pathOrCID Page Path OR CID
     * @param string $name Page Name
     * @param string $description Page Description
     * @param string $type Page Type Handle
     * @param string $template Page Template Handle
     * @param string|int|object $parent Parent Page (can be handle, ID, or object)
     * @param object $pkg Package Object
     * @param string $handle Optional slugified handle
     * @return object Page Object
     */
    protected function addPage($pathOrCID, $name, $description, $type, $template, $parent, $pkg, $handle=null)
    {
        //Get Page if it's already created
        if (is_int($pathOrCID)) {
            $page = Page::getByID($pathOrCID);
        } else {
            $page = Page::getByPath($pathOrCID);
        }
        if ($page->isError() && $page->getError() == COLLECTION_NOT_FOUND) {
            //Get Page Type and Templates from their handles
            $pageType = PageType::getByHandle($type);
            $pageTemplate = PageTemplate::getByHandle($template);
            
            //Get parent, depending on what format parent is passed in
            if (is_object($parent)) {
                $parent = $parent;
            } elseif (is_int($parent)) {
                $parent = Page::getById($parent);
            } else {
                $parent = Page::getByPath($parent);
            }
            //Get package
            $pkgID = $pkg->getPackageID();
            
            //Create Page
            $page = $parent->add($pageType, array(
                'cName' => $name,
                'cHandle' => $handle,
                'cDescription' => $description,
                'pkgID' => $pkgID,
                'cHandle' => $handle
            ), $pageTemplate);
        }
        
        return $page;
    }
    
    /**
     * Adds a Page Type with an All Publish Target (can publish anywhere)
     * @param string $typeHandle Page Type Handle
     * @param string $typeName Page Type Name
     * @param string $defaultTemplateHandle Default Page Template Handle
     * @param string $allowedTemplates (A|C|X) A for all, C for selected only, X for non-selected only
     * @param array $templateArray Array or Iterator of selected templates, see `$allowedTemplates`
     * @param object $pkg Package Object
     * @param int $startingPointCID CID of optional starting point below which page can be added
     * @param bool $selectorFormFactor Form factor of page selector
     * @return object Page Type Object
     */
    protected function addPageTypeWithAllPublishTarget($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $pkg, $startingPointCID=0, $selectorFormFactor=0)
    {
        //Get Page Type if it already exists
        $pt = PageType::getByHandle($typeHandle);
        if(!is_object($pt)) {
            //Add Page Type, then set the publishing target
            $pto = $this->addPageType($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $pkg);
            $pt = $this->setAllPublishTarget($pto, $startingPointCID, $selectorFormFactor);
        }
        
        return $pt;
    }
    
    /**
     * Add a Page Type with a Page Type Publish Target
     * @param string $typeHandle Page Type Handle
     * @param string $typeName Page Type Name
     * @param string $defaultTemplateHandle Default Page Template Handle
     * @param string $allowedTemplates (A|C|X) A for all, C for selected only, X for non-selected only
     * @param array $templateArray Array or Iterator of selected templates, see `$allowedTemplates`
     * @param int $parentPageTypeID ID of parent Page Type
     * @param object $pkg Package Object
     * @param int $startingPointCID CID of optional starting point below which page can be added
     * @param bool $selectorFormFactor Form factor of page selector
     * @return object Page Type Object
     */
    protected function addPageTypeWithPageTypePublishTarget($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $parentPageTypeID, $pkg, $startingPointCID=0, $selectorFormFactor=0)
    {
        //Get the Page Type if it already exists
        $pt = PageType::getByHandle($typeHandle);
        if(!is_object($pt)) {
            //Add the Page Type, then set the publishing target
            $pto = $this->addPageType($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $pkg);
            $pt = $this->setPageTypePublishTarget($pto, $parentPageTypeID, $startingPointCID, $selectorFormFactor);
        }
        
        return $pt;
    }
    
    /**
     * Add a Page Type with a Parent Page Publish Target
     * @param string $typeHandle Page Type Handle
     * @param string $typeName Page Type Name
     * @param string $defaultTemplateHandle Default Page Template Handle
     * @param string $allowedTemplates (A|C|X) A for all, C for selected only, X for non-selected only
     * @param array $templateArray Array or Iterator of selected templates, see `$allowedTemplates`
     * @param int $parentPageCID Parent Page CID
     * @param object $pkg Package Object
     * @return object Page Type Object
     */
    protected function addPageTypeWithParentPagePublishTarget($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $parentPageCID, $pkg) 
    {
        //Get the Page Type if it already exists
        $pt = PageType::getByHandle($typeHandle);
        if(!is_object($pt)) {
            //Add the Page Type, then set the publishing target
            $pto = $this->addPageType($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $pkg);
            $pt = $this->setParentPagePublishTarget($pto, $parentPageCID);
        }
        
        return $pt;
    }
    
    /**
     * Add New Page Type
     * @param string $typeHandle New Type Handle
     * @param string $typeName New Type Name
     * @param string $defaultTemplateHandle Default Page Template Handle
     * @param string $allowedTemplates (A|C|X) A for all, C for selected only, X for non-selected only
     * @param array $templateArray Array or Iterator of selected templates, see `$allowedTemplates`
     * @param object $pkg
     * @return object Page Type Object
     */
    protected function addPageType($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $pkg)
    {
        //Get required Template objects (these can be handles after 8)
        $defaultTemplate = PageTemplate::getByHandle($defaultTemplateHandle);
        $allowedTemplateArray = array();
        foreach($templateArray as $handle) {
            $allowedTemplateArray[] = PageTemplate::getByHandle($handle);
        }
        //Set data array for Page Type Creation
        $data = array (
            'handle' => $typeHandle,
            'name' => $typeName,
            'defaultTemplate' => $defaultTemplate,
            'allowedTemplates' => $allowedTemplates,
            'templates' => $allowedTemplateArray
        );
        $pt = PageType::add($data, $pkg);

        return $pt;
    }
    
    /**
     * Set All Pages Publish Target for Page Type
     * @param object $pageTypeObject Page Type Object 
     * @param int $startingPointCID CID of page to be underneath, or 0 for any page
     * @param bool $selectorFormFactor 1 for in page sitemap, 0 for popup sitemap
     * @return object Page Type Object
     */
    protected function setAllPublishTarget($pageTypeObject, $startingPointCID=0, $selectorFormFactor=0)
    {
        $allTarget = PublishTargetType::getByHandle('all');
        $configuredTarget = $allTarget->configurePageTypePublishTarget(
            $pageTypeObject,
            array(
            'selectorFormFactorAll' => $selectorFormFactor, // this is the form factor of the page selector. null or false is the standard sitemap popup. 1 or true would be the in page sitemap
            'startingPointPageIDall' => ($startingPointCID) // If you only want this available below a certain explicit page, but anywhere nested under that page, set this page id. null or false sets this to anywhere
            )
        );
        $pageTypeObject->setConfiguredPageTypePublishTargetObject($configuredTarget);
        
        return $pageTypeObject;
    }
    
    /**
     * Set Page Type Publish Target for Page Type
     * @param object $pageTypeObject Page Type Object
     * @param int $parentPageTypeID Parent Page Type ID
     * @param int $startingPointCID CID of page to be underneath, or 0 for any page
     * @param bool $selectorFormFactor 1 for in page sitemap, 0 for popup sitemap
     * @return object Page Type Object
     */
    protected function setPageTypePublishTarget($pageTypeObject, $parentPageTypeID, $startingPointCID=0, $selectorFormFactor=0)
    {
        $typeTarget = PublishTargetType::getByHandle('page_type');
        $configuredTypeTarget = $typeTarget->configurePageTypePublishTarget(
            $pageTypeObject, //the one being set up, NOT the target one
            array (
                'ptID' => $parentPageTypeID,
                'startingPointPageIDPageType' => $startingPointCID, // this is the form factor of the page selector. null or false is the standard sitemap popup. 1 or true would be the in page sitemap
                'selectorFormFactorPageType' => $selectorFormFactor // If you only want this available below a certain explicit page, but anywhere nested under that page, set this page id. null or false sets this to anywhere
            )
        );
        $pageTypeObject->setConfiguredPageTypePublishTargetObject($configuredTypeTarget);
        
        return $pageTypeObject;
    }
    
    /**
     * Set Parent Page Publish Target for Page Type
     * @param object $pageTypeObject Page Type Object
     * @param int $parentPageCID Parent Page CID
     * @return object Page Type Object
     */
    protected function setParentPagePublishTarget($pageTypeObject, $parentPageCID)
    {
        $parentTarget = PublishTargetType::getByHandle('parent_page');
        $configuredParentTarget = $parentTarget->configurePageTypePublishTarget(
            $pageTypeObject,
            array(
                'CParentID' => $parentPageCID
            )
         );
        $pageTypeObject->setConfiguredPageTypePublishTargetObject($configuredParentTarget);
        
        return $pageTypeObject;
    }
    
}
