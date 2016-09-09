# burrito_maker

An example defining custom field type, field widget and field formatter in Drupal 8.

# The module

Nothing fancy about the module definition as such, except for the fact that it would define a field. A standard info file with inc files for utility functions. Unlike Drupal 7, field types, field widgets and field formatters are defined and detected using _Plugin_ definitions with _annotations_ like _@FieldType_, _@FieldWidget_ and _@FieldFormatter_ respectively. Hence, we have noo _hook_field_xxx()_ implementations  in the .module file.

# The field type

The first thing to do is create a _FieldType_ plugin implementation. This class would represent a single item of the Burrito field type and hence, by convention, it has been named the [BurritoItem](src/Plugin/Field/FieldType/BurritoItem.php). Notice the _@FieldType_ part of the class documentation - that is where we have all the data which previously used to be provided by a _hook_field_info()_ implementation.

Here is some quick info on some important methods of the class (which previously used to be hook_field_* functions):

* _BurritoItem::schema()_ - Defines storage columns to be created in the database in a format similar to the one used by the Schema API.
* _BurritoItem::propertyDefinitions()_ - Defines additional info about sub-properties of the field.
* _BurritoItem::isEmpty()_ - Defines logic which determines as to when a field should be considered _empty_ (and ignored) and when the field should be considered to be containing data. In short, if this method does not return a _TRUE_, the field values would not be saved in the database.
* _BurritoItem::preSave()_ - As the name suggests, the method is called before data for a field item is saved to the database. There also exists a similar method called _::postSave()_.

After you define the field type, you can enable the module and attach an instance of the _burrito_maker_burrito_ field type to any fieldable entity. We are now 33.33333% close to our objective of having our custom burrito data!

NOTE: At times, it may seem tempting to name your field only _burrito_ instead of _burrito_maker_burrito_. However, it is good practice to define stuff in the namespace of your own module.

# The field widget

So, the database tables are ready and your field type appears in the UI. But how would the user enter data for their custom burritos? Drupal doesn't know anything about them burritos! So, we need to implement a _FieldWidget_ plugin to let tell Drupal exactly how it should build the forms for accepting burrito data.

A default widget type for our custom burrito field has already been specified in the _@FieldType_ declaration - the machine name being _burrito_default_. To define the structure of the widget, we implement [BurritoDefaultWidget](src/Plugin/Field/FieldWidget/BurritoDefaultWidget.php) with the following important method:

* _BurritoDefaultWidget::formElement()_ - This method is responsible for defining the form items which map to various schema columns provided by the field type. In our case, this method looks a bit complicated (though it is quite simple) because we have various checkboxes for the toppings/ingredients of the burrito, placed inside 2 fieldsets - _Meat_ and _Toppings_ - The meat fieldset being hidden if meat is disallowed by settings.

The _BurritoDefaultWidget::processToppingsFieldset()_ method solely exists as a helper. Its only purpose is to help flatten the user input array in such a way that the field names map to various database columns, instead of being nested under _meat_ and _toppings_ indexes in the POST array.

NOTE: One might think that adding the _#tree_ attribute to these fieldsets can help flatten the fieldset's sub-fields, but it does't work that way. If you set _#tree_ as _FALSE_ on the fieldset, though we might expect only the fieldset to be be flattened, what actually happens is every child of the fieldset is submitted as a root element in the main form. Hence, we use the _BurritoDefaultWidget::processToppingsFieldset()_ to do the trick.

# The field formatter

Input checked. Storage checked. Now for presentation of data, we define the [BurritoDefaultFormatter](src/Plugin/Field/FieldFormatter/BurritoDefaultFormatter.php). It's main purpose is to take a list _BurritoItem_ objects and display them (as per the field's display settings). This is done by the _BurritoFormatter::viewElements()_ method. Rest of the methods in the Formatter are optional but quite useful for implementing certain commonly needed features. A default formatter for our custom burrito field has already been specified in the _@FieldType_ declaration - the machine name being _burrito_default_.

NOTE: Documentation on other methods of the _BurritoDefaultFormatter_ will be added soon.
