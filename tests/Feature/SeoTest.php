<?php

namespace Tests\Feature;

use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Floor;
use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomType;
use App\Domain\Platform\Models\ContentPage;
use App\Enums\RoomStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_is_reachable_and_references_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Disallow: /admin');
        $response->assertSee('Sitemap:');
    }

    public function test_sitemap_xml_is_reachable_and_well_formed(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'sitemap.xml must be valid XML');
        $this->assertGreaterThan(0, count($xml->url));
    }

    public function test_sitemap_includes_published_policy_pages_and_active_rooms(): void
    {
        ContentPage::create(['group' => 'policy', 'key' => 'syarat-penggunaan', 'title' => 'ToS', 'is_published' => true]);

        $property = Property::create(['name' => 'P', 'slug' => 'p', 'address' => 'A', 'city' => 'C', 'province' => 'Prov']);
        $building = Building::create(['property_id' => $property->id, 'name' => 'B']);
        $floor = Floor::create(['building_id' => $building->id, 'name' => 'L1', 'level' => 1]);
        $roomType = RoomType::create(['property_id' => $property->id, 'name' => 'Std', 'slug' => 'std', 'base_price' => 1000000]);
        Room::create([
            'property_id' => $property->id, 'building_id' => $building->id, 'floor_id' => $floor->id,
            'room_type_id' => $roomType->id, 'room_number' => 'A1', 'slug' => 'a1',
            'status' => RoomStatus::Available, 'capacity' => 1, 'monthly_price' => 1000000,
            'deposit_amount' => 500000, 'is_active' => true,
        ]);

        $response = $this->get('/sitemap.xml');
        $content = $response->getContent();

        $this->assertStringContainsString('/kebijakan/syarat-penggunaan', $content);
        $this->assertStringContainsString('/living/rooms/a1', $content);
    }
}
