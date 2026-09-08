<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace core_webservice;

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for {@see token_form}.
 *
 * @package    core_webservice
 * @copyright  2026 Racem Benrhayem <racem.benrhayem@corolair.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(token_form::class)]
final class token_form_test extends \advanced_testcase {
    /**
     * Validate a token form submission for a newly created, unsuspended user.
     *
     * @param string $iprestriction The IP restriction to submit.
     * @return array The validation errors.
     */
    private function validate_iprestriction(string $iprestriction): array {
        $user = $this->getDataGenerator()->create_user();
        $form = new token_form(null, ['action' => 'create']);

        return $form->validation(['user' => $user->id, 'iprestriction' => $iprestriction], []);
    }

    /**
     * An IP restriction which address_in_subnet() understands must be accepted.
     */
    public function test_validation_accepts_a_valid_iprestriction(): void {
        $this->resetAfterTest();

        $errors = $this->validate_iprestriction('127.0.0.1, 192.168.0.0/16, fe80::1111-bbbb');

        $this->assertArrayNotHasKey('iprestriction', $errors);
    }

    /**
     * An empty IP restriction means no restriction at all, and must be accepted.
     */
    public function test_validation_accepts_an_empty_iprestriction(): void {
        $this->resetAfterTest();

        $errors = $this->validate_iprestriction('');

        $this->assertArrayNotHasKey('iprestriction', $errors);
    }

    /**
     * A domain name can never be matched by address_in_subnet(), so it must be rejected and named.
     */
    public function test_validation_rejects_a_domain_name(): void {
        $this->resetAfterTest();

        $errors = $this->validate_iprestriction('services.example.com');

        $this->assertArrayHasKey('iprestriction', $errors);
        $this->assertStringContainsString('services.example.com', $errors['iprestriction']);
    }

    /**
     * Only the invalid entries of a list are reported, and all of them are.
     */
    public function test_validation_reports_every_invalid_entry(): void {
        $this->resetAfterTest();

        $errors = $this->validate_iprestriction('127.0.0.1,nope,10.0.0.0/8,also bad');

        $this->assertArrayHasKey('iprestriction', $errors);
        $this->assertStringContainsString('nope', $errors['iprestriction']);
        $this->assertStringContainsString('also bad', $errors['iprestriction']);
        $this->assertStringNotContainsString('127.0.0.1', $errors['iprestriction']);
    }
}
