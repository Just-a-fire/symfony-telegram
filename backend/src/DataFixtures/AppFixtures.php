<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Shop;
use App\Entity\Order;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $shop = new Shop();
        $shop->setName('Мастерская букетов');
        
        $manager->persist($shop);

        for ($i = 1; $i <= 10; $i++) {
            $order = new Order();
            $order
                ->setShop($shop)
                ->setNumber('A-1' . str_pad($i, 3, "0", STR_PAD_LEFT))
                ->setTotal(rand(1000, 10000))
                ->setCustomerName("Клиент $i");
            $manager->persist($order);
        }

        $manager->flush();
    }

    private function getCustomerName(int $index): string {
        $names = [
            'Максим',
            'Альберт',
            'Светлана',
            'Татьяна',
            'Елена',
            'Ростислав',
            'Сергей',
            'Таисия',
            'Валерий',
            'Ольга'
        ];
        return $names[$index % count($names)];
    }
}
