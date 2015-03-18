<?php
/**
 * Created by PhpStorm.
 * User: joey
 * Date: 6/19/14
 * Time: 1:21 PM
 */

namespace ConnectSB\TranslationBundle\Service;

use ConnectSB\TranslationBundle\Entity\BaseTranslationKey;
use ConnectSB\TranslationBundle\Entity\BaseTranslationValue;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Yaml\Parser;

/**
 * Class DatabaseTranslationService
 * @package ConnectSB\TranslationBundle\Service
 *
 * This service is responsible for everything this bundle was created for
 */
class DatabaseTranslationService
{
    private $requestStack;
    private $phpFileCache;
    private $entityManager;
    private $kernelRootDirectory;
    private $databaseTranslationDomain;
    private $databaseTranslationsEntityString;

    private $locales = array();
    private $translations;

    public function __construct(RequestStack $requestStack, PhpFileCache $phpFileCache, EntityManager $entityManager, $kernelRootDirectory, $databaseTranslationDomain, $databaseTranslationsEntityString)
    {
        $this->requestStack = $requestStack;
        $this->phpFileCache = $phpFileCache;
        $this->entityManager = $entityManager;
        $this->kernelRootDirectory = $kernelRootDirectory;
        $this->databaseTranslationDomain = $databaseTranslationDomain;
        $this->databaseTranslationsEntityString = $databaseTranslationsEntityString;

        // Set up the Php File cache by Doctrine to use as caching mechanism
        $this->phpFileCache->setNamespace('connectsb_database_translations');
    }

    public function getDatabaseTranslationsEntity()
    {
        return $this->databaseTranslationsEntityString;
    }

    /**
     * @param ArrayCollection $translationKeysFormData
     * @return ArrayCollection
     */
    public function getModifiedTranslations(ArrayCollection $translationKeysFormData)
    {
        $translationsYamlCollection = $this->getYamlTransationsCollection();

        $modifiedTranslationsCollection = new ArrayCollection();

        /**
         * @var BaseTranslationKey $translationKeyFormData
         * @var BaseTranslationValue $translationValueFormData
         */
        foreach ($translationKeysFormData as $translationKeyFormData) {
            $translationKeyCriteria = Criteria::create()
                ->where(Criteria::expr()->eq(
                    'key', $translationKeyFormData->getKey()
                ));

            $translationYaml = $translationsYamlCollection->matching($translationKeyCriteria)->first();

            $addedToCollection = false;

            foreach ($translationKeyFormData->getTranslationValues() as $translationValueFormData) {
                $translationValueCriteria = Criteria::create()
                    ->where(Criteria::expr()->eq(
                        'value', $translationValueFormData->getValue()
                    ));

                $matchedTranslationKey = $translationYaml->getTranslationValues()->matching($translationValueCriteria)->first();

                if (!$matchedTranslationKey && !$addedToCollection) {
                    $modifiedTranslationsCollection->add($translationKeyFormData);
                    $addedToCollection = true;
                }
            }
        }

        return $modifiedTranslationsCollection;
    }

    /**
     * @return ArrayCollection
     */
    private function getYamlTransationsCollection()
    {
        $translationKeyEntityString = $this->getTranslationKeyEntity();
        $translationValueEntityString = $this->getTranslationValueEntity();

        $translationsCollection = new ArrayCollection();

        // generates a setter for the entity of the translation keys
        $explodedDatabaseTranslationsEntityString = explode(':', $this->databaseTranslationsEntityString);
        $databaseSetEntityMethod = 'set' . $explodedDatabaseTranslationsEntityString[1];

        $translationKeyEntity = $this->entityManager
            ->getRepository($this->databaseTranslationsEntityString)
            ->find($this->requestStack->getMasterRequest()->get('entityId'));

        $translationsYamlArray = $this->getTranslationsFromCache();

        foreach ($translationsYamlArray as $translationYamlKey => $translationYamlValues) {
            /** @var BaseTranslationKey $translationKey */
            $translationKey = new $translationKeyEntityString;
            $translationKey->setKey($translationYamlKey);
            $translationKey->$databaseSetEntityMethod($translationKeyEntity);

            foreach ($translationYamlValues as $translationYamlLocale => $translationYamlValue) {
                /** @var BaseTranslationValue $translationValue */
                $translationValue = new $translationValueEntityString;
                $translationValue->setLocale($translationYamlLocale);
                $translationValue->setValue($translationYamlValue);
                $translationValue->setTranslationKey($translationKey);
                $translationKey->addTranslationValue($translationValue);
            }

            $translationsCollection->add($translationKey);
        }

        return $translationsCollection;
    }

    /**
     * Get the class that extends the BaseTranslationKey entity
     *
     * @return string Name of the entity including the full namespace
     */
    public function getTranslationKeyEntity()
    {
        return $this->getEntityByParentClass('ConnectSB\\TranslationBundle\\Entity\\BaseTranslationKey', 'translation_key_entity');
    }

    /**
     * Returns the entities that extends the given entity, this entity is based the class name
     *
     * @param $className
     * @param $cacheId
     * @return null|string
     */
    private
    function getEntityByParentClass($className, $cacheId)
    {
        if ($entity = $this->phpFileCache->fetch($cacheId)) {
            return $entity;
        }

        /** @var \Doctrine\ORM\Mapping\ClassMetadata $metaDataClass */
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metaDataClass) {
            if (!$metaDataClass->getReflectionClass()->getParentClass()) {
                continue;
            }

            if ($metaDataClass->getReflectionClass()->getParentClass()->getName() == $className) {
                $entity = $metaDataClass->getName();
                $this->phpFileCache->save($cacheId, $entity);
                return $entity;
            }
        }

        return null;
    }

    /**
     * Get the class that extends the BaseTranslationValue entity
     *
     * @return string Name of the entity including the full namespace
     */
    public function getTranslationValueEntity()
    {
        return $this->getEntityByParentClass('ConnectSB\\TranslationBundle\\Entity\\BaseTranslationValue', 'translation_value_entity');
    }

    /**
     * Return all the translations from the cache if it's available in the cache
     * The reason for using the cache is because parsing YAML files over and over again would be very inefficient
     *
     * @return array
     */
    private function getTranslationsFromCache()
    {
        $translations = $this->phpFileCache->fetch('all_translations');

        if ($translations) {
            return $translations;
        }

        $translations = $this->getTranslationsArray();

        $this->phpFileCache->save('all_translations', $translations);

        return $translations;
    }

    /**
     * Reads all YAML files based on the domain that was set by the external party
     * Also gets the amount of locales and saves this into the cache.
     *
     * Relies on the fact that translations should be in the Resources/translations folder
     */
    private function getTranslationsArray()
    {
        $finder = new Finder();
        $finder->files()->name($this->databaseTranslationDomain . '.*.yml');

        $parser = new Parser();

        /** @var SplFileInfo $file */
        foreach ($finder->in($this->kernelRootDirectory . '/../src/*/*/Resources/translations') as $file) {
            $explodedFilename = explode('.', $file->getRelativePathname());

            $locale = $explodedFilename[1];

            if (!in_array($locale, $this->locales)) {
                array_push($this->locales, $locale);
            }

            $parsedTranslations = $parser->parse(file_get_contents($file->getPathname()));
            $this->buildTranslationsArrayRecursively($parsedTranslations, '', $locale);
        }

        $this->phpFileCache->save('amount_locales', $this->locales);

        return $this->translations;
    }

    /**
     * When the YAML files are parsed they are parsed in an inconvenient way for use in the bundle
     * That is why this function is called on every YAML file to modify the layout of the PHP array
     * which is returned when parsing a YAML file.
     *
     * @param $array
     * @param string $keysString
     * @param $locale
     */
    private function buildTranslationsArrayRecursively($array, $keysString = '', $locale)
    {
        if (!is_array($array)) {
            $this->translations[$keysString][$locale] = $array;
            return;
        }

        foreach ($array as $key => $value) {
            $allKeys = $keysString . $key . '.';

            if (!is_array($value)) {
                $allKeys = $keysString . $key;
            }

            if (is_numeric($key)) {
                $allKeys = $keysString;
            }

            $this->buildTranslationsArrayRecursively($value, $allKeys, $locale);
        }
    }

    /**
     * Gets all the translations read from the YAML files with the specified domain.
     *
     * @return ArrayCollection all translations read from the YAML files with the specified domain
     */
    public function getTranslationsCollection()
    {
        $translationsYamlCollection = $this->getYamlTransationsCollection();

        /** @var ArrayCollection $translationKeysDatabase */
        $translationsFromDatabase = $this->entityManager
            ->getRepository($this->getTranslationKeyEntity())
            ->getTranslationKeysFromDatabase($this->databaseTranslationsEntityString, $this->requestStack->getMasterRequest()->get('entityId'));

        if (!$translationsFromDatabase) {
            return $translationsYamlCollection;
        }

        /** @var BaseTranslationKey $translationFromDatabase */
        foreach ($translationsFromDatabase as $translationFromDatabase) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq(
                    'key', $translationFromDatabase->getKey()
                ));

            $translationYaml = $translationsYamlCollection->matching($criteria);

            $translationsYamlCollection->set($translationYaml->key(), $translationFromDatabase);
        }

        return $translationsYamlCollection;
    }

    /**
     * Gets all of the locales for the translations
     *
     * @return array All locales for the translations
     */
    public function getLocales()
    {
        if ($locales = $this->phpFileCache->fetch('amount_locales')) {
            return $locales;
        }

        return $this->locales;
    }
}
