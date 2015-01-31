<?php
/**
 * Created by PhpStorm.
 * User: joey
 * Date: 6/19/14
 * Time: 3:39 PM
 */

namespace ConnectSB\TranslationBundle\Translation;

use ConnectSB\TranslationBundle\Entity\BaseTranslationKey;
use ConnectSB\TranslationBundle\Entity\BaseTranslationValue;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Class DatabaseTranslationLoader
 * @package ConnectSB\TranslationBundle\Translation
 *
 * This class is responsible for loading the modified translations into the catalogue.
 * The domain of the catalogue is messages, that is why the domain to load translations from can't be messages.
 */
class DatabaseTranslationLoader implements LoaderInterface
{
    private $requestStack;
    private $entityManager;
    private $databaseTranslationsEntityString;

    /**
     * @param RequestStack $requestStack
     * @param EntityManager $entityManager
     * @param $databaseTranslationsEntityString
     */
    public function __construct(RequestStack $requestStack, EntityManager $entityManager, $databaseTranslationsEntityString)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->databaseTranslationsEntityString = $databaseTranslationsEntityString;
    }

    /**
     * This function is called at startup when in twig the trans filter is called.
     *
     * The translations that are loaded are specified by the entityId
     * which should be set in the POST parameters.
     *
     * If you make sure that entityId is set at startup all translations will be loaded
     * and will be usable throughout the whole application
     *
     * @param $resource
     * @param $locale
     * @param string $domain
     * @return MessageCatalogue
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $entityId = $this->requestStack->getMasterRequest()->get('entityId');

        $catalogue = new MessageCatalogue($locale);

        if ($entityId && $this->databaseTranslationsEntityString) {

            $translationsFromDatabase = $this->entityManager
                ->getRepository($this->getTranslationKeyEntity())
                ->getTranslationKeysFromDatabase($this->databaseTranslationsEntityString, $this->requestStack->getMasterRequest()->get('entityId'));

            $translationReplacements = array();

            /** @var BaseTranslationKey $translationFromDatabase */
            /** @var BaseTranslationValue $translationValue */
            foreach ($translationsFromDatabase as $translationFromDatabase) {
                $translationValue = $translationFromDatabase->getTranslationValueByLocale($locale);
                if (!$translationValue) {
                    continue;
                }

                $translationReplacements[$translationFromDatabase->getKey()] = $translationValue->getValue();
            }

            $catalogue->replace($translationReplacements);
        }

        return $catalogue;
    }

    /**
     * Get the class that extends the BaseTranslationKey entity
     *
     * @return string Name of the entity including the full namespace
     */
    public function getTranslationKeyEntity()
    {
        return $this->getEntityByParentClass('ConnectSB\\TranslationBundle\\Entity\\BaseTranslationKey');
    }

    /**
     * Returns the entities that extends the given entity, this entity is based the class name
     *
     * @param $className
     * @return null|string
     */
    private function getEntityByParentClass($className)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $metaDataClass */
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metaDataClass) {
            if (!$metaDataClass->getReflectionClass()->getParentClass()) {
                continue;
            }

            if ($metaDataClass->getReflectionClass()->getParentClass()->getName() == $className) {
                $entity = $metaDataClass->getName();
                return $entity;
            }
        }

        return null;
    }
}
