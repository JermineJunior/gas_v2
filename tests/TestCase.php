<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * RefreshDatabase يحذف جداول قاعدة البيانات المستهدفة بالكامل،
     * لذلك نرفض تشغيل أي اختبار إلا على قاعدة بيانات تنتهي بـ _test
     * (مثل gas_v2_test) حتى لا يُمسّت قاعدة البيانات الحقيقية أبدًا.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $default = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$default}.database");

        if (! is_string($database) || ! str_ends_with($database, '_test')) {
            throw new \RuntimeException(
                "Refusing to run tests against database [{$database}] — DB_DATABASE must end with _test (see phpunit.xml)."
            );
        }

        return $app;
    }
}
