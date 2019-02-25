<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompanyProduct
 *
 * @ORM\Table(name="company_product")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\CompanyProductRepository")
 */
class CompanyProduct
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="stock_quantity", type="integer")
     */
    private $stockQuantity = 10;

    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Company" )
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Product" )
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }



    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set stockQuantity
     *
     * @param integer $stockQuantity
     *
     * @return CompanyProduct
     */
    public function setStockQuantity($stockQuantity)
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    /**
     * Get stockQuantity
     *
     * @return int
     */
    public function getStockQuantity()
    {
        return $this->stockQuantity;
    }
}

