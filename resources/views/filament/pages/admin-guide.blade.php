@php
    use App\Filament\Pages\CatalogImport;
    use App\Filament\Resources\CategoryResource;
    use App\Filament\Resources\ConsultationResource;
    use App\Filament\Resources\ProductResource;
    use App\Filament\Resources\QuoteResource;

    $quickActions = [
        [
            'title' => 'Import CSV',
            'ar' => 'ارفع ملف المنتجات والتصنيفات',
            'en' => 'Upload catalog data and read the report.',
            'href' => CatalogImport::getUrl(),
        ],
        [
            'title' => 'Products',
            'ar' => 'عدّل المنتجات وارفع الصور يدوي',
            'en' => 'Manage product content, specs, pricing, and media.',
            'href' => ProductResource::getUrl(),
        ],
        [
            'title' => 'Categories',
            'ar' => 'رتّب شجرة التصنيفات',
            'en' => 'Create parent and child categories.',
            'href' => CategoryResource::getUrl(),
        ],
        [
            'title' => 'Quotes',
            'ar' => 'راجع الطلبات واطبعها وكلم العميل',
            'en' => 'Read buyer requests and follow up safely.',
            'href' => QuoteResource::getUrl(),
        ],
        [
            'title' => 'Consultations',
            'ar' => 'افتح رسائل التواصل واتابع واتساب',
            'en' => 'Review contact submissions and respond.',
            'href' => ConsultationResource::getUrl(),
        ],
    ];

    $goldenRules = [
        ['ar' => 'الطلبات والرسائل قراءة فقط', 'en' => 'Quotes and consultations are read-only.'],
        ['ar' => 'مفيش حذف للمنتجات أو التصنيفات', 'en' => 'Disable content with inactive state instead of deletion.'],
        ['ar' => 'كل منتج في CSV لازم يكون له SKU', 'en' => 'Every import row needs a real SKU.'],
        ['ar' => 'صور المنتجات يدوي فقط في المرحلة دي', 'en' => 'Product images are uploaded manually in this phase.'],
    ];

    $csvSteps = [
        ['ar' => 'افتح Catalog CSV Import وحمّل التمبلت.', 'en' => 'Open Catalog CSV Import and download the template.'],
        ['ar' => 'املأ البيانات في Excel واحفظ الملف CSV.', 'en' => 'Fill the sheet in Excel and save it as CSV.'],
        ['ar' => 'راجع sku و name_ar قبل الرفع. دول أهم عمودين.', 'en' => 'Check sku and name_ar before upload. These are the critical columns.'],
        ['ar' => 'اكتب التصنيف والساب تصنيف بالعربي. النظام هيعمل الناقص تلقائي.', 'en' => 'Use Arabic category and subcategory names. Missing categories are created safely.'],
        ['ar' => 'أي عمود يبدأ بـ spec_ هيتخزن كمواصفة فنية.', 'en' => 'Any column starting with spec_ becomes a technical spec.'],
        ['ar' => 'ارفع الملف واقرأ تقرير النتائج قبل ما تقفل الصفحة.', 'en' => 'Upload the file and read the report before leaving the page.'],
        ['ar' => 'أي صف فشل، صلحه في الملف وارفع تاني. النظام مش بيعمل منتج وهمي.', 'en' => 'Fix failed rows and re-import. The system will not create fake products.'],
    ];

    $csvColumns = [
        ['column' => 'sku', 'ar' => 'مطلوب لكل منتج. لو موجود قبل كده، المنتج يتحدث.', 'en' => 'Required. Existing SKU updates the product.'],
        ['column' => 'name_ar', 'ar' => 'اسم المنتج العربي. مطلوب لأنه اللغة الأساسية.', 'en' => 'Required Arabic product name.'],
        ['column' => 'name_en', 'ar' => 'اختياري للإنجليزي.', 'en' => 'Optional English product name.'],
        ['column' => 'category_ar', 'ar' => 'التصنيف الرئيسي بالعربي.', 'en' => 'Arabic parent category.'],
        ['column' => 'subcategory_ar', 'ar' => 'اختياري. لو موجود المنتج يترابط عليه.', 'en' => 'Optional child category. Product should link to it when present.'],
        ['column' => 'base_price', 'ar' => 'اختياري. السعر لا يظهر للزائر غير المسجل.', 'en' => 'Optional. Guest users still do not receive numeric prices.'],
        ['column' => 'is_active', 'ar' => 'اختياري. المنتجات الجديدة تدخل غير مفعلة لو العمود مش محدد active.', 'en' => 'Optional. New products default to inactive unless explicitly active.'],
        ['column' => 'spec_material', 'ar' => 'مثال لمواصفة. ينفع تضيف spec_height أو spec_finish.', 'en' => 'Example spec column. Add spec_height, spec_finish, and more as needed.'],
    ];

    $contentChecks = [
        ['ar' => 'استخدم parent category للتصنيفات الفرعية.', 'en' => 'Use parent category for child categories.'],
        ['ar' => 'اربط المنتج بأدق تصنيف متاح.', 'en' => 'Link each product to the most specific category available.'],
        ['ar' => 'استخدم sort order لترتيب الظهور.', 'en' => 'Use sort order to control display order.'],
        ['ar' => 'لو المنتج مش جاهز، خليه inactive بدل الحذف.', 'en' => 'Mark unfinished products inactive instead of deleting them.'],
        ['ar' => 'ارفع صور المنتج من صفحة المنتج نفسه.', 'en' => 'Upload product media from the product page only.'],
    ];
@endphp

<x-filament-panels::page>
    <div class="space-y-8">
        <section class="overflow-hidden rounded-2xl bg-slate-950 text-white ring-1 ring-white/10">
            <div class="grid gap-8 p-6 lg:grid-cols-[1.05fr_0.95fr] lg:p-8">
                <div class="space-y-5">
                    <div class="inline-flex rounded-full border border-cyan-300/30 bg-cyan-300/10 px-3 py-1 text-xs font-semibold text-cyan-100">
                        Elshbiny Omega Operating Guide
                    </div>
                    <div class="space-y-3">
                        <h2 class="max-w-3xl text-2xl font-bold tracking-tight sm:text-3xl">
                            دليل تشغيل لوحة الشبيني
                        </h2>
                        <p class="max-w-3xl text-sm leading-7 text-slate-200 sm:text-base">
                            الصفحة دي معمولة علشان أي حد يمسك لوحة <strong>/omega</strong> يعرف يشتغل بأمان:
                            يرفع منتجات، يراجع الطلبات، يطبع، يتواصل مع العميل، ويعرف بالظبط إيه الممنوع يتلمس.
                        </p>
                    </div>
                    <div class="max-w-3xl rounded-xl border border-white/10 bg-white/5 p-4" dir="ltr">
                        <p class="text-sm leading-6 text-slate-200">
                            This guide keeps Omega practical: catalog content can be managed, buyer-submitted records stay protected,
                            and CSV import remains the fastest way to load large product lists without risking bad data.
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($quickActions as $action)
                        <a
                        class="group rounded-xl border border-white/10 bg-white/5 p-4 transition hover:border-cyan-300/50 hover:bg-cyan-300/10"
                            href="{{ $action['href'] }}"
                        >
                            <div class="text-sm font-bold text-white">{{ $action['title'] }}</div>
                            <div class="mt-2 text-sm leading-6 text-slate-200">{{ $action['ar'] }}</div>
                            <div class="mt-2 text-xs leading-5 text-slate-400" dir="ltr">{{ $action['en'] }}</div>
                            <div class="mt-3 text-xs font-semibold text-cyan-200">Open section</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($goldenRules as $rule)
                <div class="rounded-xl border border-amber-500/20 bg-amber-50 p-4 text-amber-950 dark:bg-amber-500/10 dark:text-amber-100">
                    <div class="text-sm font-bold">{{ $rule['ar'] }}</div>
                    <div class="mt-2 text-xs leading-5 text-amber-800 dark:text-amber-200" dir="ltr">{{ $rule['en'] }}</div>
                </div>
            @endforeach
        </div>

        <x-filament::section>
            <x-slot name="heading">Start Here / ابدأ من هنا</x-slot>
            <x-slot name="description">اختار المسار حسب اللي أنت محتاج تعمله دلوقتي.</x-slot>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-xl border border-gray-200 p-5 dark:border-white/10">
                    <h3 class="text-base font-bold">لو معاك ملف منتجات</h3>
                    <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-300">
                        روح على <strong>Catalog CSV Import</strong>، حمّل التمبلت، املأه، ارفعه، واقرأ التقرير.
                        أي صف فشل يتصلح ويتعاد رفعه.
                    </p>
                    <p class="mt-3 text-xs leading-5 text-gray-500" dir="ltr">
                        Best for bulk category and product updates.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 p-5 dark:border-white/10">
                    <h3 class="text-base font-bold">لو عميل بعت طلب</h3>
                    <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-300">
                        افتح <strong>Quotes</strong>، ادخل View، اطبع الطلب أو افتحه في الواجهة، وكلم العميل من زر واتساب لو الرقم موجود.
                    </p>
                    <p class="mt-3 text-xs leading-5 text-gray-500" dir="ltr">
                        Quotes are read-only. Do not edit status or submitted data.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 p-5 dark:border-white/10">
                    <h3 class="text-base font-bold">لو عميل بعت رسالة</h3>
                    <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-300">
                        افتح <strong>Consultations</strong>، راجع الرسالة، اطبعها عند الحاجة، واستخدم رسالة واتساب الجاهزة للمتابعة.
                    </p>
                    <p class="mt-3 text-xs leading-5 text-gray-500" dir="ltr">
                        Contact submissions are also read-only.
                    </p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">CSV Import Playbook / دليل رفع ملف المنتجات</x-slot>
            <x-slot name="description">ده أهم جزء في الدليل، لأنه أسرع طريق لإدخال داتا كبيرة من العميل.</x-slot>

            <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <div class="space-y-4">
                    @foreach ($csvSteps as $index => $step)
                        <div class="flex gap-3 rounded-xl border border-gray-200 p-4 dark:border-white/10">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-cyan-600 text-sm font-bold text-white">
                                {{ $index + 1 }}
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-semibold leading-6">{{ $step['ar'] }}</p>
                                <p class="text-xs leading-5 text-gray-500 dark:text-gray-400" dir="ltr">{{ $step['en'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                        <h3 class="text-sm font-bold">CSV Columns / أعمدة مهمة</h3>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($csvColumns as $item)
                            <div class="grid gap-3 px-4 py-3 md:grid-cols-[0.28fr_0.72fr]">
                                <code class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-cyan-700 dark:bg-white/10 dark:text-cyan-200">
                                    {{ $item['column'] }}
                                </code>
                                <div class="space-y-1">
                                    <p class="text-sm leading-6">{{ $item['ar'] }}</p>
                                    <p class="text-xs leading-5 text-gray-500 dark:text-gray-400" dir="ltr">{{ $item['en'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-red-500/20 bg-red-50 p-4 text-red-950 dark:bg-red-500/10 dark:text-red-100">
                <p class="text-sm font-bold">مهم جدًا</p>
                <p class="mt-2 text-sm leading-7">
                    لو <code>sku</code> ناقص، الصف يفشل ويتكتب في التقرير. ده مقصود علشان مايتعملش منتج غلط أو مكرر.
                    المنتجات الجديدة تدخل <strong>inactive</strong> بشكل افتراضي، وبعد المراجعة تتفعل يدويًا.
                </p>
                <p class="mt-2 text-xs leading-5 text-red-800 dark:text-red-200" dir="ltr">
                    Missing SKU rows fail by design. New products default to inactive until reviewed.
                </p>
            </div>
        </x-filament::section>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">Products + Categories / المنتجات والتصنيفات</x-slot>
                <x-slot name="description">إدارة المحتوى مسموحة، الحذف مؤجل. الإخفاء يتم من الحالة.</x-slot>

                <div class="space-y-4">
                    @foreach ($contentChecks as $check)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
                            <p class="text-sm font-semibold leading-6">{{ $check['ar'] }}</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400" dir="ltr">{{ $check['en'] }}</p>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Quotes + Consultations / الطلبات والرسائل</x-slot>
                <x-slot name="description">البيانات اللي جاية من العميل تتشاف وتتطبع ويتعمل عليها متابعة، لكنها لا تتعدل ولا تتمسح.</x-slot>

                <div class="space-y-4">
                    <div class="rounded-xl border border-gray-200 p-5 dark:border-white/10">
                        <h3 class="text-base font-bold">Quotes</h3>
                        <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-300">
                            افتح الطلب من View. هتلاقي شريط أدوات للفتح في الواجهة، الطباعة، وواتساب.
                            حالة الطلب بتتحدث من السيستم فقط، مش من الأدمن.
                        </p>
                        <p class="mt-3 text-xs leading-5 text-gray-500" dir="ltr">
                            Admin can inspect, print, and follow up. Manual editing is disabled.
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-5 dark:border-white/10">
                        <h3 class="text-base font-bold">Consultations</h3>
                        <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-300">
                            افتح الرسالة من View. تقدر تطبع، تفتح واتساب لو فيه رقم، وتنسخ التليفون أو الإيميل من صفحة العرض.
                        </p>
                        <div class="mt-4 rounded-lg bg-gray-100 p-4 text-sm leading-7 dark:bg-white/10">
                            <p class="font-bold">موقع الشبيني يتحدث</p>
                            <p>أهلاً بحضرتك، وصلنا طلبك من موقع الشبيني ونتابع معاك بخصوصه.</p>
                        </div>
                        <p class="mt-3 text-xs leading-5 text-gray-500" dir="ltr">
                            This is the prepared WhatsApp follow-up message.
                        </p>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">Delivery Safety / قواعد التسليم</x-slot>
            <x-slot name="description">نقط صغيرة تمنع مشاكل كبيرة وقت التسليم أو التشغيل اليومي.</x-slot>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <h3 class="text-sm font-bold">Access</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                        <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-white/10">/omega</code>
                        للـ super admin فقط. حسابات المشترين لا تدخل اللوحة.
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <h3 class="text-sm font-bold">Prices</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                        الأسعار لا تظهر للزائر. المسجل يرى المسموح فقط، و hidden يفضل مخفي.
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <h3 class="text-sm font-bold">Images</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                        مفيش bulk image import في المرحلة الحالية. الصور من صفحة المنتج.
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <h3 class="text-sm font-bold">Before Launch</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                        غير كلمات مرور الحسابات، راجع
                        <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-white/10">.env</code>،
                        وجرب رفع CSV صغير قبل ملف العميل الكبير.
                    </p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
