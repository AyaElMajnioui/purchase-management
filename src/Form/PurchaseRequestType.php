<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\PurchaseRequest;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class)
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantity',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                ],
            ])
            ->add('justification')
            ->add('attachment', FileType::class, [
                'label' => 'Attachment (PDF)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => PurchaseRequest::STATUS_PENDING,
                    'Approved' => PurchaseRequest::STATUS_APPROVED,
                    'Rejected' => PurchaseRequest::STATUS_REJECTED,
                ],
                'required' => true,
            ])
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('requester', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a product',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PurchaseRequest::class,
        ]);
    }
}
