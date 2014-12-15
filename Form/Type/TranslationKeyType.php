<?php
/**
 * Created by PhpStorm.
 * User: joey
 * Date: 6/19/14
 * Time: 4:05 PM
 */

namespace ConnectSB\TranslationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TranslationsType
 * @package ConnectSB\TranslationBundle\Form\
 *
 * This class will render the form that is used when editing an translation
 */
class TranslationKeyType extends AbstractType
{
    private $databaseTranslationKeyEntity;
    private $databaseTranslationValueEntity;

    public function __construct($databaseTranslationKeyEntity, $databaseTranslationValueEntity)
    {
        $this->databaseTranslationKeyEntity = $databaseTranslationKeyEntity;
        $this->databaseTranslationValueEntity = $databaseTranslationValueEntity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translationValues', 'collection', array(
                'label' => false,
                'type' => new TranslationValueType($this->databaseTranslationValueEntity),
                'allow_add' => false,
                'allow_delete' => false,
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->databaseTranslationKeyEntity
        ));
    }

    public function getName()
    {
        return 'database_translations';
    }
}
