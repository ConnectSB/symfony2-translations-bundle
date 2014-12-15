<?php
/**
 * Created by PhpStorm.
 * User: joey
 * Date: 6/19/14
 * Time: 3:39 PM
 */

namespace ConnectSB\TranslationBundle\Translation;

use ConnectSB\InstantWinBundle\Entity\TranslationKey;
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
    private $entityManager;
    private $requestStack;
    private $databaseTranslationsEntity;

    /**
     * @param EntityManager $entityManager
     * @param RequestStack $requestStack
     * @param $databaseTranslationsEntity
     */
    public function __construct(EntityManager $entityManager, RequestStack $requestStack, $databaseTranslationsEntity)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->databaseTranslationsEntity = $databaseTranslationsEntity;
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

        if ($entityId && $this->databaseTranslationsEntity) {

            /** @var TranslationKey[] $translationKeys */
            $translationKeys = $this->entityManager
                ->getRepository($this->databaseTranslationsEntity)
                ->find($entityId)
                ->getTranslationKeys();

            $translationReplacements = array();

            /** @var BaseTranslationKey $translationKey */
            /** @var BaseTranslationValue $translationValue */
            foreach ($translationKeys as $translationKey) {
                $translationValue = $translationKey->getTranslationValueByLocale($locale);
                if (!$translationValue) {
                    continue;
                }

                $translationReplacements[$translationKey->getKey()] = $translationValue->getValue();
            }

            $catalogue->replace($translationReplacements);
        }

        return $catalogue;
    }
}
