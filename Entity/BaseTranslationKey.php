<?php
/**
 * Created by PhpStorm.
 * User: jimmy
 * Date: 4/15/14
 * Time: 2:54 PM
 */

namespace ConnectSB\TranslationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BaseTranslationKey
 * @package ConnectSB\TranslationBundle\Entity
 *
 * This entity should be extended by the external party, it holds the keys for the translations
 *
 * @ORM\MappedSuperclass(repositoryClass="ConnectSB\TranslationBundle\Repository\TranslationKeyRepository")
 */
class BaseTranslationKey
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="translation_key", type="string", unique=true)
     */
    protected $key;

    protected $translationValues;

    public function __construct()
    {
        $this->translationValues = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key
     *
     * @param string $key
     * @return BaseTranslationKey
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @param string $locale
     * @return BaseTranslationValue|null
     */
    public function getTranslationValueByLocale($locale)
    {
        /** @var BaseTranslationValue $translationValue */
        foreach ($this->translationValues as $translationValue) {
            if ($translationValue->getLocale() == $locale) {
                return $translationValue;
            }
        }
        return null;
    }

    /**
     * Get translationValues
     *
     * @return ArrayCollection
     */
    public function getTranslationValues()
    {
        return $this->translationValues;
    }

    /**
     * @param ArrayCollection $translationValues
     */
    public function setTranslationValues($translationValues)
    {
        $this->translationValues = $translationValues;
    }

    /**
     * Add translationValues
     *
     * @param BaseTranslationValue $translationValues
     * @return BaseTranslationKey
     */
    public function addTranslationValue(BaseTranslationValue $translationValues)
    {
        $this->translationValues[] = $translationValues;

        return $this;
    }

    /**
     * Remove translationValues
     *
     * @param BaseTranslationValue $translationValues
     */
    public function removeTranslationValue(BaseTranslationValue $translationValues)
    {
        $this->translationValues->removeElement($translationValues);
    }
}
