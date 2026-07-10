<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $path = public_path('assets/json/languages.json');
        $exsting_langauges = json_decode(file_get_contents($path), true);
        // add german
        $exsting_langauges['de'] = [
            "name" => "Deutsch",
            "flag" => "de",
            "rtl" => false,
            "enabled" => true
        ];

        // add indonesian
        $exsting_langauges['id'] = [
            "name" => "Indonesian",
            "flag" => "id",
            "rtl" => false,
            "enabled" => true
        ];

        // add japanese
        $exsting_langauges['ja'] = [
            "name" => "Japanese",
            "flag" => "jp",
            "rtl" => false,
            "enabled" => true
        ];

        file_put_contents($path, json_encode($exsting_langauges, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //remove
        $langs = ['de', 'id', 'ja'];
        $path = public_path('assets/json/languages.json');
        $exsting_langauges = json_decode(file_get_contents($path), true);
        foreach ($langs as $lang) {
            unset($exsting_langauges[$lang]);
        }
        file_put_contents($path, json_encode($exsting_langauges, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
};
