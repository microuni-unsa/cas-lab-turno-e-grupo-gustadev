Category Changer Component
==========================
The Category Changer retrieves the information what has in the current category
entry after changes have been submitted.
It creates an construct what has changed for the current object. Additionally if there is
any kind of object browser which has a backwards category then changes are being created
for the old assigned object and for the new assigned object.

At the moment it only works with **POST** data. In future it will also hanlde the changes
while importing data where only an array and entry ID is needed.

## Usage
It is pretty simple how it works. Just call the static instance method from **idoit\Module\Cmdb\Component\CategoryChanges\Changes**
and set the available parameters. And call the method **processChanges()** which processes
for the current object and if available for the old and new assigned object.

**Example:**

```php
// With $_POST data
$changes = idoit\Module\Cmdb\Component\CategoryChanges\Changes::instance(
    $categoryDao,
    $objectId,
    null,
    [],
    [],
    $smartyData,
    $postData
);
$changes->processChanges();

// @todo With array data
$changes = idoit\Module\Cmdb\Component\CategoryChanges\Changes::instance(
    $categoryDao,
    $objectId,
    $entryId,
    $currentData,
    $changedData
);
$changes->processChanges();

// Optional way
$dataProvider = new idoit\Module\Cmdb\Component\CategoryChanges\Data\DataProvider($objectId);
$dataProvider
    ->setEntryId($entryId)
    ->setCurrentData(DefaultData::factory($dao, $currentData))
    ->setChangedData(DefaultData::factory($dao, $changedData))
    ->setSmartyData(SmartyData::factory($smartyData))
    ->setPropertyData(PropertyData::factory($categoryDao))
    ->setRequestData(RequestData::factory($requestData));
    
$changes = new idoit\Module\Cmdb\Component\CategoryChanges\Changes($categoryDao);
$changes->setDataProvider($dataProvider);
$changes->processChanges();
```

There are 3 different methods which access the changes of the current object, 
the old assigned object and new assigned object.

```php
// return value would be an object of type
// idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesDataCollection
$currentChangesCollection = $changes->getCurrentChangesCollection();
$fromChangesCollection = $changes->getFromChangesCollection();
$toChangesCollection = $changes->getToChangesCollection();

// to retrieve the reformated data as an array
$currentChangesReformated = $changes->getCurrentReformatedChanges();
$fromChangesReformated = $changes->getFromReformatedChanges();
$toChangesReformated = $changes->getToReformatedChanges();
```
