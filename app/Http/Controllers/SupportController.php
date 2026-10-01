<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SupportController extends Controller
{
    /**
     * Display the support and help desk center.
     */
    public function index(): View
    {
        $faqs = Schema::hasTable('faqs')
            ? Faq::active()->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        $contactEmail = Schema::hasTable('settings')
            ? \App\Models\Setting::get('contact_email', 'support@vsite.ng')
            : 'support@vsite.ng';

        $contactPhone = Schema::hasTable('settings')
            ? \App\Models\Setting::get('contact_phone', '+234 800 000 0000')
            : '+234 800 000 0000';

        $contactWhatsapp = Schema::hasTable('settings')
            ? \App\Models\Setting::get('contact_whatsapp', '+234 812 345 6789')
            : '+234 812 345 6789';

        return view('support.index', compact('faqs', 'contactEmail', 'contactPhone', 'contactWhatsapp'));
    }
}
