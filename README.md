# Concrete5 Tutorial - Programmatic Page Types
Concrete5.7+ example code for creating Page Types and setting Publish Types programmatically.

## Installation
Copy the `page_type_example` folder into the packages folder in the root of your Concrete5 site. Then go to the Dashboard->Extend Concrete5 and then click install next to the Programmatic Page Types Example package.

After installation, go to Dashboard->Pages & Themes->Page Types. You will find the News Item and News Item File example Page Types installed there.

## Notes
This is a slightly more abstracted function than is shown in the [Adding Page Types Progammatically](http://documentation.concrete5.org/developers/working-with-pages/adding-page-types-programmatically) tutorial.
In this version, the addition of sets and keys are moved into their own functions that can be easily re-used and which allows for installing all types of Publish Types within the methods.
