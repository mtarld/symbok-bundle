/**
 * @var int
 */
private $id;
/**
 * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
 * @ORM\JoinColumn(nullable=true)
 * @Symbok\Getter(nullable=true)
 * @Symbok\Setter(noAdd=true)
 */
private $prices;
/**
 * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
 * @Symbok\Getter()
 * @Symbok\Setter(fluent=true)
 */
private $category;
public function addPrice($price)
{
    $this->prices->add($price);
}
/**
 * Retrieves prices
 *
 * @return \Doctrine\Common\Collections\Collection|null
 */
public function getPrices() : ?\Doctrine\Common\Collections\Collection
{
    return $this->prices;
}
/**
 * Sets prices
 *
 * @param \Doctrine\Common\Collections\Collection|null $prices
 * @return void
 */
public function setPrices(?\Doctrine\Common\Collections\Collection $prices)
{
    $prices->setProduct2($this);
    $this->prices = $prices;
}
/**
 * Removes price from prices
 *
 * @param \App\Entity\Price $price
 * @return void
 */
public function removePrice(\App\Entity\Price $price)
{
    if ($this->prices->contains($price)) {
        $this->prices->removeElement($price);
        $price->removeProduct2($this);
    }
}
/**
 * Retrieves category
 *
 * @return \App\Entity\Category
 */
public function getCategory() : \App\Entity\Category
{
    return $this->category;
}
/**
 * Sets category
 *
 * @param \App\Entity\Category $category
 * @return self
 */
public function setCategory(\App\Entity\Category $category) : self
{
    $this->category->removeProduct2($this);
    if ($category) {
        $category->addProduct2($this);
    }
    $this->category = $category;
    return $this;
}