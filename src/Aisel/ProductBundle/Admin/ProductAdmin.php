<?php

/*
 * This file is part of the Aisel package.
 *
 * (c) Ivan Proskuryakov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aisel\ProductBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Sonata\AdminBundle\Validator\ErrorElement;

/**
 * Product CRUD configuration for Backend
 *
 * @author Ivan Proskoryakov <volgodark@gmail.com>
 */
class ProductAdmin extends Admin
{
    protected $productManager;
    protected $baseRoutePattern = 'product';

    public function setManager($productManager)
    {
        $this->productManager = $productManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('name')
            ->assertNotBlank()
            ->end()
            ->with('descriptionShort')
            ->assertNotBlank()
            ->end()
            ->with('metaUrl')
            ->assertNotBlank()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper

            ->with('General')
            ->add('name', 'text', array('label' => 'Name', 'attr' => array()))
            ->add('sku', 'text', array('label' => 'Sku', 'attr' => array()))
            ->add('descriptionShort', 'ckeditor',
                array(
                    'label' => 'Short Description',
                    'required' => true,
                    'attr' => array('class' => 'field-content')
                ))
            ->add('description', 'ckeditor',
                array(
                    'label' => 'Description',
                    'required' => true,
                    'attr' => array('class' => 'field-content')
                ))
            ->with('Pricing')
            ->add('price', 'money', array('label' => 'Price', 'attr' => array()))
            ->add('priceSpecial', 'money', array('label' => 'Special Price', 'required' => false, 'attr' => array()))
            ->add('priceSpecialFrom', 'datetime', array('label' => 'Special Price From', 'attr' => array()))
            ->add('priceSpecialTo', 'datetime', array('label' => 'Special Price To', 'attr' => array()))
            ->add('new', 'choice', array('choices' => array(
                '0' => 'Disabled',
                '1' => 'Enabled'),
                'label' => 'New', 'attr' => array()))
            ->add('newFrom', 'datetime', array('label' => 'New From', 'attr' => array()))
            ->add('newTo', 'datetime', array('label' => 'New To', 'attr' => array()))
            ->with('Categories', array('description' => 'Select related categories'))
            ->add('categories', 'gedmotree', array('expanded' => true, 'multiple' => true,
                'class' => 'Aisel\CategoryBundle\Entity\Category',
            ))
            ->with('Images')
            ->with('Meta', array('description' => 'Meta description for search engines'))
            ->add('metaUrl', 'text', array('label' => 'Url', 'required' => true, 'help' => 'note: URL value must be unique'))
            ->add('metaTitle', 'text', array('label' => 'Title', 'required' => false))
            ->add('metaDescription', 'textarea', array('label' => 'Description', 'required' => false))
            ->add('metaKeywords', 'textarea', array('label' => 'Keywords', 'required' => false))
            ->end();

    }

    public function getFormTheme()
    {
        return array('AiselAdminBundle:Form:form_admin_fields.html.twig');
    }

    public function prePersist($product)
    {
        $url = $product->getMetaUrl();
        $normalUrl = $this->productManager->normalizeProductUrl($url);

        $product->setMetaUrl($normalUrl);
        $product->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $product->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
    }

    public function preUpdate($product)
    {
        $url = $product->getMetaUrl();
        $productId = $product->getId();
        $normalUrl = $this->productManager->normalizeProductUrl($url, $productId);

        $product->setMetaUrl($normalUrl);
        $product->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name')
            ->add('price')
            ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    ))
            );;
    }

    /**
     * @param \Sonata\AdminBundle\Show\ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Information')
            ->add('content')
            ->add('updatedAt')
            ->add('status', 'boolean')
            ->with('Categories')
            ->add('categories', 'tree')
            ->with('Meta')
            ->add('metaUrl')
            ->add('metaTitle')
            ->add('metaDescription')
            ->add('metaKeywords')
            ->with('General')
            ->add('id');
    }

    /**
     * {@inheritdoc}
     */
    public function toString($object)
    {
        return $object->getId() ? $object->getSku() : $this->trans('link_add', array(), 'SonataAdminBundle');
    }
}