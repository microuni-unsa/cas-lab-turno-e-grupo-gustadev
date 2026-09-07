# i-doit Processor component

The CMDB processors should be used to cread, read, update or rank object types, objects or categories (WIP).
They are easy to use and contain all necessary steps, necessary to perform the action.

For example: if you'd like to create an object, it is NOT ENOUGH to simply call `$dao->insert_new_obj(...)` because this will only perform the
raw database insertion. But at this point no rights have been checked, no logbook was written, no signals / events have been dispatched...
Similar things need to happen when objects or categories get updated, ranked and so on...

## Knowledge base

A knowledge base entry will be written, as soon as the component is released.

## Usage

The CMDB processors live in the DI container, you can access them anywhere by using the CMDB factory:

```php
/** @var idoit\Component\Factory\CmdbFactory $factory */
$factory = isys_application::instance()->container->get('cmdb.factory');

/** @var \idoit\Component\Processor\ObjectTypeGroup\ObjectTypeGroupProcessor $objectProcessor */
$objectTypeGroupProcessor = $factory->getObjectTypeGroupProcessor();

/** @var \idoit\Component\Processor\ObjectType\ObjectTypeProcessor $objectProcessor */
$objectTypeProcessor = $factory->getObjectTypeProcessor();

/** @var \idoit\Component\Processor\Object\ObjectProcessor $objectProcessor */
$objectProcessor = $factory->getObjectProcessor();
```

The processors contain similar methods to interact with their corresponding data. Please note that we use `DTO` objects for read and write actions.

## Reading

When reading via `read` or `readById` you will receive a corresponding `ReadResponse` object that contains the entries:

```php
$result = $objectTypeProcessor->readById(5); // Reading 'Server' object type
```

When dumping the `$result` variable you will see something similar to

```php
\idoit\Component\Processor\Dto\ObjectType\ReadResponse::__set_state([
   'entries' => [
        0 => \idoit\Component\Processor\Dto\ObjectType\Dto::__set_state([
            'id' => 5,
            'objectTypeGroup' => \idoit\Component\Processor\Dto\Shape\DialogShape::__set_state([
              'id' => 2,
              'title' => 'Infrastructure',
              'titleRaw' => 'LC__CMDB__OBJTYPE_GROUP__INFRASTRUCTURE',
              'constant' => 'C__OBJTYPE_GROUP__INFRASTRUCTURE',
           ]),
           'title' => 'Server',
           'titleRaw' => 'LC__CMDB__OBJTYPE__SERVER',
           'constant' => 'C__OBJTYPE__SERVER',
           'description' => '',
           'image' => '/cmdb/object-type/image/5',
           'icon' => '/cmdb/object-type/icon/5',
           'specificCategory' => NULL,
           'isSelfDefined' => false,
           'isContainer' => false,
           'isPositionableInRack' => true,
           'isVisible' => true,
           'isRelationMaster' => false,
           'hasOverviewPage' => true,
           'sort' => 26,
           'color' => '#a2bcfa',
           'defaultTemplate' => NULL,
           'status' => \idoit\Component\Processor\Dto\Shape\StatusShape::__set_state([
              'id' => 2,
              'title' => 'Normal',
              'titleRaw' => 'LC__CMDB__RECORD_STATUS__NORMAL',
              'constant' => 'C__RECORD_STATUS__NORMAL',
           ]),
           'sysidPrefix' => '',
           'assignedCategories' => [],
           'categoriesOnOverviewPage' => [],
        ]),
  ],
])
```

Plain scalar values will be available 'as they are'. Reference values, like `objectTypeGroup` or `status` will be wrapped in a corresponding object.

## Creating / Updating

We provide specific `DTO` classes for creating and updating. The benefits of this are autocompletion, type hinting and safety.
The `DTO` classes also contain meta information about the properties which will be used to properly validate any given data.
