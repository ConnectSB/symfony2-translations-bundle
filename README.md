# Symfony Translation Bundle

With this Bundle you can edit defined translations in Symfony2. The edited translations are stored in two entities in your database.

## 1) Installation
First you have to add the folowing lines to your `composer.json` file:
```javascript
{
    "require": {
        "connectsb/translationbundle": "dev-master"
    }
}
```
You also have to add the TranslationBundle to your AppKernel.php:
```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new ConnectSB\TranslationBundle\ConnectSBTranslationBundle()
        );
    }
}
```
## 2) Usage

In `config.yml` you have to set the following parameters:
```
connect_sb_translation:
    database_translations_domain:   # name of the translation file (can't be messages)
    database_translations_entity:   # name of the entity for the translations
```
This bundle relies on two entities, BaseTranslationKey & BaseTranslationValue. The key entity contains the keys of the translations while BaseTranslationValue contains the actual value of the key plus the locale.

The reason why you should extend both these entities is because you will have to define a relationship between the translations and your own entity.

An example of the class to extend BaseTranslationKey is:
```php
/**
 * @ORM\Table()
 * @ORM\Entity
 */
class TranslationKey extends BaseTranslationKey
{
    /**
     * @ORM\OneToMany(targetEntity="TranslationValue", mappedBy="translationKey", cascade={"persist"})
     */
    protected $translationValues;

    /**
     * Define connection with your entity here
     *
     * @ORM\ManyToOne(targetEntity="ExampleEntity", inversedBy="translationKeys", cascade={"persist"})
     * @ORM\JoinColumn(name="example_entity_id", referencedColumnName="id")
     */
    private $exampleEntity;
}
```
Second you should extend the BaseTranslationValue class, an example of this class would look like:
```php
/**
 * @ORM\Table()
 * @ORM\Entity
 */
class TranslationValue extends BaseTranslationValue
{
    /**
     * @ORM\ManyToOne(targetEntity="TranslationKey", inversedBy="translationValues", cascade={"persist"})
     * @ORM\JoinColumn(name="translation_key_id", referencedColumnName="id")
     */
    protected $translationKey;
}
```

The getters and setters are needed for both entities. Doctrine can create those getters and setters for you.
You should execute the following command when located at the root of your project: `php app/console doctrine:generate:entities [nameOfYourFolder]`, where nameOfYourFolder should be the top folder of your bundle.

### 3) Forms
This bundle contains a number of Forms. The Forms can be used to edit the translations. An example is shown below:
```php
public function editTranslations(Request $request, ExampleEntity $exampleEntity)
{
    // Set the entity ID in the request so the bundle can read it
    $request->request->set('entityId', $exampleEntity->getId());

    /** TranslationKey[] $translations */
    $translations = $this->get('connect_sb_database_translation_service')->getTranslationsCollection();

    // Set the translationKeys (this is necessary in order to build the form)
    $exampleEntity->setTranslationKeys($translations);

    $form = $this->createForm(
        new TranslationsCollectionType($this->get('connect_sb_database_translation_service')), $exampleEntity
    );
    
    $form->handleRequest($request);

    if ($form->isValid()) {
        // Get the (edited) translationKeys from the form
        $translationKeys = $form->getData()->getTranslationKeys();

        /** TranslationKey[] $modifiedTranslationKeys */
        $modifiedTranslationKeys = $this->get('connect_sb_database_translation_service')->getModifiedTranslations($translationKeys);
        $exampleEntity->setTranslationKeys($modifiedTranslationKeys);
      
        // persist and flush
    }
      // return and render the form
}
```
