<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\DataTransformer\FilenameToFileTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ImageType extends AbstractType
{
    private $transformer;

    public function __construct(FilenameToFileTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('fileName', FileType::class, [
                'label' => 'Image (.png or .jpg)',
                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PNG or JPG file',
                    ]),
                ],
                // validation message if the data transformer fails
                'invalid_message' => 'That is not a valid filename',
            ]);

        $builder->get('fileName')
                ->addModelTransformer($this->transformer);
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            [$this, 'onPostSetData']
        );
    
    }

    public function onPostSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();
        if ($data) {
            if($data instanceof Image){
                if($data->getFileName() === null){
                    return;
                }
            }
            $form->remove('fileName');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
