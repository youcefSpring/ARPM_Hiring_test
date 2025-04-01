<?php

namespace Tests\Unit;

use App\Jobs\ProcessProductImage;
use App\Models\Product;
use App\Services\SpreadsheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class SpreadsheetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $spreadsheet_service;
    protected string $file_path;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->spreadsheet_service = new SpreadsheetService();
        $this->file_path = 'file_path.xlsx';
    }

    /**
     * Test if valid data is correctly processed and stored in the database.
     */
    public function test_processSpreadsheet_with_valid_data()
    {
        Queue::fake();

        // Mock the importer service
        $mock_importer = Mockery::mock();
        $mock_importer->shouldReceive('import')->andReturn([
            ['product_code' => 'ABC123', 'quantity' => 10],
            ['product_code' => 'XYZ456', 'quantity' => 5],
        ]);

        app()->instance('importer', $mock_importer);

        // Process the spreadsheet file using the variable
        $this->spreadsheet_service->processSpreadsheet($this->file_path);

        // Verify that the products are stored correctly in the database
        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseHas('products', ['code' => 'ABC123', 'quantity' => 10]);
        $this->assertDatabaseHas('products', ['code' => 'XYZ456', 'quantity' => 5]);

        // Ensure the job was dispatched for each product
        Queue::assertPushed(ProcessProductImage::class, 2);
    }

    /**
     * Test that invalid data is skipped and not stored in the database.
     */
    public function test_processSpreadsheet_skips_invalid_data()
    {
        Queue::fake();

        // Mock the importer service with invalid data
        $mock_importer = Mockery::mock();
        $mock_importer->shouldReceive('import')->andReturn([
            ['product_code' => 'ABC123', 'quantity' => 10], // Valid
            ['product_code' => 'XYZ456', 'quantity' => -1], // Invalid quantity
            ['product_code' => null, 'quantity' => 5],      // Missing product_code
        ]);

        app()->instance('importer', $mock_importer);

        // Process the spreadsheet file using the variable
        $this->spreadsheet_service->processSpreadsheet($this->file_path);

        // Only the valid product should be stored
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', ['code' => 'ABC123', 'quantity' => 10]);

        // Ensure only the valid product gets a job dispatched
        Queue::assertPushed(ProcessProductImage::class, 1);
    }

    /**
     * Test that duplicate product codes are skipped.
     */
    public function test_processSpreadsheet_handles_duplicate_product_code()
    {
        Queue::fake();

        // Create an existing product in the database
        Product::factory()->create(['code' => 'ABC123', 'quantity' => 10]);

        // Mock the importer service with a duplicate product code
        $mock_importer = Mockery::mock();
        $mock_importer->shouldReceive('import')->andReturn([
            ['product_code' => 'ABC123', 'quantity' => 5], // Duplicate product_code
            ['product_code' => 'XYZ456', 'quantity' => 3], // Valid
        ]);

        app()->instance('importer', $mock_importer);

        // Process the spreadsheet file using the variable
        $this->spreadsheet_service->processSpreadsheet($this->file_path);

        // Only the new valid product should be stored
        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseHas('products', ['code' => 'XYZ456', 'quantity' => 3]);

        // Ensure the job is only dispatched for the new valid product
        Queue::assertPushed(ProcessProductImage::class, 1);
    }
}
