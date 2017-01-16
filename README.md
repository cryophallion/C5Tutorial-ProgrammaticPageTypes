# Concrete5 Tutorial - Programmatic Page Types
Concrete5.7+ example code for creating Page Types and setting Publish Types programmatically.

## Installation
Copy the `page_type_example` folder into the packages folder in the root of your Concrete5 site. Then go to the Dashboard->Extend Concrete5 and then click install next to the Programmatic Page Types Example package.

After installation, go to Dashboard->Pages & Themes->Page Types. You will find the News Item and News Item File example Page Types installed there.

## Notes
This is a slightly more abstracted function than is shown in the [Adding Page Types Progammatically](http://documentation.concrete5.org/developers/working-with-pages/adding-page-types-programmatically) tutorial.
In this version, the addition of sets and keys are moved into their own functions that can be easily re-used and which allows for installing all types of Publish Types within the methods.

# UPDATE
Due to documentation for other files being deleted from the Concrete5 documentation site, I am adding it inline here:

## Background
One of the largest benefits of the change to 5.7 was the decoupling of Page Types from Page Templates. Now, you can easily have a blog page that can look many different ways. Additionally, it allows people to easily create composer pages for end users, to easily fill in information and have the information, attributes, look, and style of the pages be consistent, without them needing to know what blocks go where and with what advanced template, etc. In fact, this allows many sites that would have needed to be designed like single page sites earlier to more easily be converted into the C5 page logic.

## Example Specification
For this example, a simple page type will be added. This will not cover the addition of composer forms, with blocks and sets, as not all packages will need that functionality. For the purposes of this example, we will be adding a page that will only be allowed to use the left side template, and which will be for listing news items. Since news items only make sense below a News Page Type, we will also set it so that the option to add a news item only is below a news page.

## Adding the Page Type
### Requirements
Before we can add the Page Type, we will need to first call in the required namespaces in the head of the controller. Both the Page Type and Page Template namespaces will be required:

    use PageType;
    use PageTemplate;
    use \Concrete\Core\Page\Type\PublishTarget\Type\Type as PublishTargetType;
    
### Getting the Page Templates
Now we can begin to create our page types in our controller's install and upgrade function (or preferably, another function that both call. First, we will need to get the handles for the default template this Page Type will use, and the Allowed Templates it is allowed to use (NOTE: currently, you MUST pass the PageTemplate objects themselves to the creation of the Page Type, however this may be allowed to be handles for 8.0.0 and beyond).

Since we will just be using the left sidebar template, that is the only one we need to get:

    $left_side = PageTemplate::getByHandle('left_sidebar');
    
And with that, we can pass the required information to the Page Type adding function. This is done with a data array. However, first, we should ensure that the Page Type doesn't already exist. Additionally, in this case, we will take the object created and assign it to the variable, in case later on Composer information or other features need to be added.

    $newsItem = PageType::getByHandle('news_item');
    if (!is_object($newsItem)) {
        $newsItem = PageType::add(
            array(
                'handle' => 'news_item',
                'name' => 'News Item', //Note: it does not appear you can pass the t() function in the name
                'defaultTemplate' => $left_side, // optional item, but wise to add
                'allowedTemplates' => 'C', //A is all, C is selected only, X is not selected only, all referring to the next key, defaults to A if key is not included
                'templates' => array($left_side), //So, in this case, with C above, ONLY left sidebar can be used
                'ptLaunchInComposer' => false, //optional, defaults to false, but good to know the key in case it needs to be true
                'ptIsFrequentlyAdded' => true //optional, defaults to false, and whether or not it shows up in the add page type frequent list
            ),
            $pkg //this would come from the install or upgrade function usually
        );
    }
    
And now we have a News Item Page Type.

## Setting Publish Methods
Next is making sure that news items only get published below a news Page Type. This is done by getting a publish target, in this case, the page type publish target:

    $newsTarget = PublishTargetType::getByHandle('page_type');
    
And then we need the News Page Type ID:

    $newsId = PageType::getByHandle('news')->getPageTypeID();

And now we can do one of two things: we can either go through all the steps of creating the configuration, or we can forge a post request to set it up for us (the requirements for which are under the PublishTarget\Type namespace in the API, looking at the configurePageTypePublishTarget function). For the sake of ease, it's likely best to just fake the post. 

    $configuredNews = $newsTarget->configurePageTypePublishTarget(
        $newsItem,
        array(
            'ptID' => $newsId,
            'selectorFormFactorPageType' => null //just sets the default sitemap selector
        )
    );
    
And then we use that configured target to set up the child Page Type:

    $newsItem->setConfiguredPageTypePublishTargetObject($configuredNews);
    
And now we have a News Item Page Type, that only allows the Left Sidebar Template, and which can only be published below a News Page Type.

##Appendix
To save people the trouble of searching the code to determine the proper values to pass to the configuration, here is the info on each of the types. :

###All Type

    $allTarget = PublishTargetType::getByHandle('all');
    $configuredAllTarget = $allTarget->configurePageTypePublishTarget(
        $pageTypeObject,
        array(
            'selectorFormFactorAll' => null, // this is the form factor of the page selector. null or false is the standard sitemap popup. 1 or true would be the in page sitemap
            'startingPointPageIDall' => (cID) // If you only want this available below a certain explicit page, but anywhere nested under that page, set this page id. null or false sets this to anywhere
        )
    );
    $pageType->setConfiguredPageTypePublishTargetObject($configuredAllTarget);
    
###Page Type Type
This is shown in the example, but for completeness, is included here.

    $typeTarget = PublishTargetType::getByHandle('page_type');
    $configuredTypeTarget = $typeTarget->configurePageTypePublishTarget(
        $pageTypeObject, //the one that is being set as a child, NOT the type being targeted
        array(
            'ptID' => (ptID), //Page Type ID of the targeted, or parent, Page Type
            'startingPointPageIDPageType => (cID), // See All for an explanation of this
            'selectorFormFactorPageType' => null //See All for an explanation of this
        )
    );
    $pageType->setConfiguredPageTypePublishTargetObject($configuredTypeTarget);

###Parent Page Type

    $parentTarget = PublishTargetType::getByHandle('parent_page');
    $configuredPageTarget = $parentTarget->configurePageTypePublishTarget(
        $pageTypeObject,
        array(
            'cParentID' => (cID) //ID of the parent page
        )
    );
    $pageType->setConfiguredPageTypePublishTargetObject($configuredPageTarget);
