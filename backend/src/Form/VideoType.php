<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('videoFile', FileType::class, [
                'label' => 'Vidéo (MP4)',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir une vidéo.']),
                    new File([
                        // Limite technique (taille). La limite "durée" est gérée séparément.
                        // Ajuste si besoin selon ton serveur/PHP.
                        'maxSize' => '200M',
                        'mimeTypes' => [
                            'video/mp4',
                            // optionnel: 'video/webm',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une vidéo MP4 valide.',
                        'maxSizeMessage' => 'La vidéo est trop volumineuse (max {{ limit }}).',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
