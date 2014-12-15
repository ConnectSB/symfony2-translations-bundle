<?php
/**
 * Created by PhpStorm.
 * User: jimmy
 * Date: 4/15/14
 * Time: 2:54 PM
 */

namespace ConnectSB\TranslationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BaseTranslationValue
 * @package ConnectSB\TranslationBundle\Entity
 *
 * This entity should be extended by the external party, it holds the keys for the translations
 *
 * @ORM\MappedSuperclass()
 */
class BaseTranslationValue
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
     * @ORM\Column(name="translation_value", type="text")
     */
    protected $value;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string")
     */
    protected $locale;

    protected $translationKey;

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
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return BaseTranslationValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return BaseTranslationValue
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTranslationKey()
    {
        return $this->translationKey;
    }

    public function setTranslationKey($translationKey)
    {
        $this->translationKey = $translationKey;
    }
}
