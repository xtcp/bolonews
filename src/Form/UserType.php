<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function __construct(private readonly Security $security) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options'  => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => ['autocomplete' => 'nouveau-motdepasse'],
                ],
                'second_options' => [
                    'label' => 'Nouveau mot de passe (confirmation)',
                    'attr' => ['autocomplete' => 'nouveau-motdepasse'],
                ],
                'invalid_message' => 'Les deux mot de passes doit être identiques.',
                'constraints' => [
                    new Assert\Length(
                        min: 6,
                        minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} characters',
                        max: 4096,
                    ),
                ],
                'help' => 'Laisses ce champ vide si vous ne voulez pas changer votre mot de passe',
            ])
        ;
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
