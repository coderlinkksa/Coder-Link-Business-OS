<?php

namespace Tests\Unit\Modules\CRM\Domain;

use App\Modules\CRM\Domain\Enums\LeadSource;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadSourceTest extends TestCase
{
    #[Test]
    public function it_has_exactly_five_sources(): void
    {
        $this->assertCount(5, LeadSource::cases());
    }

    #[Test]
    public function sources_can_be_created_from_string_value(): void
    {
        $this->assertSame(LeadSource::Website,     LeadSource::from('website'));
        $this->assertSame(LeadSource::Referral,    LeadSource::from('referral'));
        $this->assertSame(LeadSource::Direct,      LeadSource::from('direct'));
        $this->assertSame(LeadSource::SocialMedia, LeadSource::from('social_media'));
        $this->assertSame(LeadSource::Other,       LeadSource::from('other'));
    }

    #[Test]
    public function each_source_has_a_human_readable_label(): void
    {
        $this->assertSame('Website',      LeadSource::Website->label());
        $this->assertSame('Referral',     LeadSource::Referral->label());
        $this->assertSame('Direct',       LeadSource::Direct->label());
        $this->assertSame('Social Media', LeadSource::SocialMedia->label());
        $this->assertSame('Other',        LeadSource::Other->label());
    }
}
