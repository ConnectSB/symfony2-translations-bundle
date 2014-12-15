<?php
/**
 * Created by PhpStorm.
 * User: joey
 * Date: 6/19/14
 * Time: 4:05 PM
 */

namespace ConnectSB\TranslationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TranslationsType
 * @package ConnectSB\TranslationBundle\Form\
 *
 * This class will render the form that is used when editing an translation
 */
class TranslationValueType extends AbstractType
{
    private $databaseTranslationValueEntity;

    public function __construct($databaseTranslationValueEntity)
    {
        $this->databaseTranslationValueEntity = $databaseTranslationValueEntity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', 'text', array(
            'label' => false,
            'constraints' => array(
                new NotBlank()
            )
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->databaseTranslationValueEntity
        ));
    }

    public function getName()
    {
        return 'database_translations';
    }
}
