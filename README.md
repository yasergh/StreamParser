<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## Stream Parser For Laravel Framework

> **Note:** This repository contains the core code of convert XML file to array.


## Installation
```
composer require snono/stream-parser
```

## Recommended usage
Delegate as possible the callback execution so it doesn't blocks the document reading: 
## Practical Input/Code/Output demos

### XML
```xml
<Products Version="1.00">
    <Product>
        <ProductId>31774</ProductId>
        <Name><![CDATA[Giliola Mor Pembe Detaylı Çocuk Spor Ayakkabı]]></Name>
        <ShortDescription/>
        <MetaTitle>Giliola Mor Pembe Detaylı Çocuk Spor Ayakkabı</MetaTitle>
        <SKU>ANT-CCK-CSP-133124</SKU>
        <Gtin>3177431774317</Gtin>
        <AlisFiyati>40.00</AlisFiyati>
        <Price>40.00</Price>
        <Published>True</Published>
        <ProductCombinations>
            <ProductCombination>
                <ProductCombinationId>...</ProductCombinationId>
                <SKU>...</SKU>
                <Gtin>3031774317743</Gtin>
                <StockQuantity>...</StockQuantity>
                <ProductAttributes>...</ProductAttributes>
            </ProductCombination>
            <ProductCombination>
                <ProductCombinationId>1782578</ProductCombinationId>
                <SKU>ANT-CCK-CSP-133124</SKU>
                <Gtin>3331774317743</Gtin>
                <StockQuantity>0</StockQuantity>
                <ProductAttributes>
                    <ProductAttribute>
                        <Name>Çocuk Numara</Name>
                        <Value>33</Value>
                    </ProductAttribute>
                </ProductAttributes>
            </ProductCombination>
        </ProductCombinations>
        <Pictures>
            <Picture>
                <PictureUrl>https://www.xmlcim.com/image/catalog/erbilden/Y5Lnj9L9HVD15ciAS1r2xkSAospZcG3Qmk1fY.jpg</PictureUrl>
            </Picture>
            <Picture>
                <PictureUrl>https://www.xmlcim.com/image/catalog/erbilden/FOnQWf6dPYqTDSDqhMcUhwSIIHoI57oolYj1V.jpg</PictureUrl>
            </Picture>
        </Pictures>
        <Categories>
            <Category>
                <CategoryId>288</CategoryId>
                <Name>Çocuk Spor</Name>
                <CategoryPath>...</CategoryPath>
            </Category>
        </Categories>
        <Manufacturers>
            <Manufacturer>
                <Id>7</Id>
                <Name><![CDATA[Erbilden]]></Name>
            </Manufacturer>
        </Manufacturers>
        <ProductSameColors/>
    </Product>
</Products>
```

```php
    use Snono\StreamParser\XMLParser;

    $objXML = new XMLParser();
    $arr =  $objXML->setUrl('http://localhost/product.xml')
                   ->xmlParser()
                   ->mapping(
                        array(
                                'Products' => array(
                                    'Product' => array(
                                                'id_product' => 'ProductId',
                                                'sku' => 'SKU',
                                                'qty' => 'StockQuantity',
                                                'title' => 'MetaTitle',
                                                'images' => 'Pictures.Picture.PictureUrl',
                                                'category' => 'Categories.Category.CategoryId:Name:CategoryPath',
                                                'manufacturer' => 'Manufacturers.Manufacturer',
                                                'productCombinations' => 'ProductCombinations.ProductCombination.StockQuantity:SKU:ProductCombinationId:ProductAttributes',
                                    )
                                )
                            )
                        )
                   ->toArray();
```
```
[
  {
    "id_product": "31774",
    "sku": "ANT-CCK-CSP-133124",
    "qty": "7",
    "title": "Giliola Mor Pembe Detaylı Çocuk Spor Ayakkabı",
    "images": [
      {
        "PictureUrl": "https://www.xmlcim.com/image/catalog/erbilden/Y5Lnj9L9HVD15ciAS1r2xkSAospZcG3Qmk1fY.jpg"
      },
      {
        "PictureUrl": "https://www.xmlcim.com/image/catalog/erbilden/FOnQWf6dPYqTDSDqhMcUhwSIIHoI57oolYj1V.jpg"
      },
      {
        "PictureUrl": "https://www.xmlcim.com/image/catalog/erbilden/4JYDn1jVXJdLHaUaQ11QyX0rQjLOHk4seRfF3.jpg"
      },
      {
        "PictureUrl": "https://www.xmlcim.com/image/catalog/erbilden/k6oVbvFRlxd3SdQCCcZLovODTi4V5xkZutrzj.jpg"
      }
    ],
    "category": {
      "CategoryId": "288",
      "Name": "Çocuk Spor",
      "CategoryPath": "Çocuk Ayakkabı &gt;&gt; Çocuk Spor"
    },
    "manufacturer": {
      "Id": "7",
      "Name": "Erbilden"
    },
    "productCombinations": [
      {
        "ProductCombinationId": "1782575",
        "SKU": "ANT-CCK-CSP-133124",
        "Gtin": "3031774317743",
        "StockQuantity": "1",
        "ProductAttributes": {
          "ProductAttribute": {
            "Name": "Çocuk Numara",
            "Value": "30"
          }
        }
      }
    ]
  },
  {
    "id_product": "31763",
    "sku": "ANT-CCK-CSP-133113",
    "qty": "7",
    "title": "Giliola Mor Çocuk Spor Ayakkabı ",
    "images": [
      {
        "PictureUrl": "https://www.xmlcim.com/image/catalog/erbilden/xvspaJFco5uFRGJtjf3LG6wtVCWtQVYsENuBy.jpg"
      },
      {
        "PictureUrl": "https://www.xmlcim.com/image/catalog/erbilden/UfLqiVkJi6XzuBEdgGfZuxtfuHcLSJhlsgN3U.jpg"
      },
      {
        "PictureUrl": "https://www.xmlcim.com/image/catalog/erbilden/vR0fTLvE69rmScPQQ062z0kxp8oQzNMyHVUtG.jpg"
      },
      {
        "PictureUrl": "https://www.xmlcim.com/image/catalog/erbilden/T5tphhfeCTYvFc5NzWVqHDB8XVRXJkWOYTOIw.jpg"
      }
    ],
    "category": {
      "CategoryId": "288",
      "Name": "Çocuk Spor",
      "CategoryPath": "Çocuk Ayakkabı &gt;&gt; Çocuk Spor"
    },
    "manufacturer": {
      "Id": "7",
      "Name": "Erbilden"
    },
    "productCombinations": [
      {
        "ProductCombinationId": "1782515",
        "SKU": "ANT-CCK-CSP-133113",
        "Gtin": "2631763317633",
        "StockQuantity": "1",
        "ProductAttributes": {
          "ProductAttribute": {
            "Name": "Çocuk Numara",
            "Value": "26"
          }
        }
      }
    ]
  }
]
```

### Data Source two type
From URL link use code 
```php
    $objXML->setUrl('http://localhost/product.xml');
```

Or load data from local file 
```php
    $objXML->setFileName('./tmp/product.xml');
```

### Return all data without mapping 
```php
    $arr = $objXML->getContent();
```
