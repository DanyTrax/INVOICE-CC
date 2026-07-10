# Graph Report - INVOICE-CC-ACCIONCOL  (2026-07-09)

## Corpus Check
- 317 files · ~305,599 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 6128 nodes · 18613 edges · 327 communities (298 shown, 29 thin omitted)
- Extraction: 88% EXTRACTED · 12% INFERRED · 0% AMBIGUOUS · INFERRED: 2317 edges (avg confidence: 0.72)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `dbefecf3`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 2
- Community 3
- Community 4
- Community 5
- Community 6
- Community 7
- Community 8
- Community 9
- Community 10
- Community 11
- Community 12
- Community 13
- Community 14
- Community 15
- Community 16
- Community 17
- Community 18
- Community 19
- Community 20
- Community 21
- Community 22
- Community 23
- Community 24
- Community 25
- Community 26
- Community 27
- Community 28
- Community 29
- Community 30
- Community 31
- Community 32
- Community 33
- Community 34
- Community 35
- Community 36
- Community 37
- Community 38
- Community 39
- Community 40
- Community 41
- Community 42
- Community 43
- Community 44
- Community 45
- Community 46
- Community 47
- Community 48
- Community 49
- Community 50
- Community 51
- Community 52
- Community 53
- Community 54
- Community 55
- Community 56
- Community 57
- Community 58
- Community 59
- Community 60
- Community 61
- Community 62
- Community 63
- Community 64
- Community 65
- Community 66
- Community 67
- Community 68
- Community 69
- Community 70
- Community 71
- Community 72
- Community 73
- Community 74
- Community 75
- Community 76
- Community 77
- Community 78
- Community 79
- hd
- _each
- Community 82
- hd
- Community 84
- Community 85
- Community 86
- Community 87
- Community 88
- Community 89
- Community 90
- Community 91
- Community 92
- getContext
- Community 94
- Community 95
- Community 96
- Community 97
- Community 98
- Community 99
- Community 100
- Community 101
- Community 102
- Community 103
- Community 104
- Community 105
- Community 106
- Community 107
- Community 108
- Community 109
- Community 110
- Community 111
- Community 112
- Community 113
- Community 114
- Community 115
- Community 116
- Community 117
- Community 118
- Community 119
- Community 120
- Community 121
- Community 122
- Community 123
- Community 127
- Community 128
- Community 129
- Community 130
- Community 131
- Community 132
- Community 133
- Community 134
- Community 135
- Community 136
- Community 181
- Community 200
- Community 217
- Community 218
- Community 219
- Community 220
- Community 221
- Community 222
- Community 223
- Community 228
- Community 246
- Community 247
- Community 248
- Community 249
- Community 252
- Community 253
- Community 254
- Community 255
- Community 258

## God Nodes (most connected - your core abstractions)
1. `r()` - 192 edges
2. `i()` - 170 edges
3. `t()` - 159 edges
4. `a()` - 145 edges
5. `update()` - 138 edges
6. `constructor()` - 136 edges
7. `u()` - 132 edges
8. `o()` - 116 edges
9. `y()` - 98 edges
10. `resolve()` - 90 edges

## Surprising Connections (you probably didn't know these)
- `up()` --calls--> `PermissionService`  [INFERRED]
  database/migrations/2026_03_25_120000_seed_role_permissions_matrix_defaults.php → app/Services/PermissionService.php
- `up()` --calls--> `PermissionService`  [INFERRED]
  database/migrations/2026_07_10_000002_seed_invoice_module_permissions.php → app/Services/PermissionService.php
- `up()` --calls--> `LegalPageDefaults`  [INFERRED]
  database/settings/2026_04_01_000000_add_legal_html_to_general_settings.php → app/Support/LegalPageDefaults.php
- `up()` --calls--> `process()`  [INFERRED]
  database/migrations/2026_04_01_120000_add_company_code_abbreviation_and_process_solicitud_code.php → composer-setup.php
- `up()` --calls--> `RolePermission`  [INFERRED]
  database/migrations/2026_03_25_120000_seed_role_permissions_matrix_defaults.php → app/Models/RolePermission.php

## Import Cycles
- None detected.

## Communities (327 total, 29 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.01
Nodes (128): ac(), acceptToken(), ak(), allows(), attrs(), [b.Blockquote](), [b.ListItem](), baseTheme() (+120 more)

### Community 1 - "Community 1"
Cohesion: 0.05
Nodes (132): d(), o(), p(), h(), Ab(), addGlobalAttributes(), addKeyboardShortcuts(), addMark() (+124 more)

### Community 2 - "Community 2"
Cohesion: 0.01
Nodes (91): abutsStart(), Bh(), co(), cr(), di(), diffNow(), du(), ed() (+83 more)

### Community 3 - "Community 3"
Cohesion: 0.03
Nodes (156): accept(), ad(), add(), addChunk(), addEventListener(), addInfoPane(), addInner(), addToSet() (+148 more)

### Community 4 - "Community 4"
Cohesion: 0.01
Nodes (246): Aa(), Ac(), accepts(), ad(), add(), addExtensions(), addHackNode(), addNode() (+238 more)

### Community 5 - "Community 5"
Cohesion: 0.02
Nodes (100): Eo(), addControllers(), addEventListener(), addPlugins(), addScales(), al(), as(), bindResponsiveEvents() (+92 more)

### Community 6 - "Community 6"
Cohesion: 0.05
Nodes (114): addSingleBadge(), addSingleSelectionDisplay(), Ae(), ai(), applyDisabledState(), b(), be(), bi() (+106 more)

### Community 7 - "Community 7"
Cohesion: 0.07
Nodes (17): InvoiceController, RedirectResponse, Request, Response, View, StoreInvoiceRequest, UpdateInvoiceRequest, InvoiceMail (+9 more)

### Community 8 - "Community 8"
Cohesion: 0.04
Nodes (130): addAttributes(), addProseMirrorPlugins(), after(), Ah(), Au(), ax(), bc(), before() (+122 more)

### Community 9 - "Community 9"
Cohesion: 0.04
Nodes (102): aa(), addChild(), addCompletion(), addCompletions(), addNamespace(), addNamespaceObject(), ah(), AP() (+94 more)

### Community 10 - "Community 10"
Cohesion: 0.05
Nodes (79): acquireContext(), addBox(), adjustHitBoxes(), afterDraw(), bn(), bs(), buildOrUpdateScales(), clear() (+71 more)

### Community 11 - "Community 11"
Cohesion: 0.04
Nodes (104): themeClasses(), ai(), al(), ao(), Ba(), bf(), bo(), br() (+96 more)

### Community 12 - "Community 12"
Cohesion: 0.03
Nodes (111): after(), ag(), al(), Am(), Ao(), as(), before(), bm() (+103 more)

### Community 13 - "Community 13"
Cohesion: 0.12
Nodes (9): LogAdminMutationActivity, Closure, Request, Response, ActivityLogService, Model, Request, Response (+1 more)

### Community 14 - "Community 14"
Cohesion: 0.04
Nodes (84): ai(), gu(), addElements(), ae(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit() (+76 more)

### Community 15 - "Community 15"
Cohesion: 0.03
Nodes (103): addBlockWidget(), addBreak(), addComposition(), addDelimiter(), addInlineWidget(), addLine(), addLineStart(), addLineStartIfNotCovered() (+95 more)

### Community 16 - "Community 16"
Cohesion: 0.13
Nodes (10): PermissionController, RedirectResponse, Request, BelongsTo, RoleHierarchy, BelongsTo, RolePermission, up() (+2 more)

### Community 17 - "Community 17"
Cohesion: 0.07
Nodes (22): checkParams(), checkPlatform(), displayHelp(), ErrorHandler, getHomeDir(), getIniMessage(), getOptValue(), getPlatformIssues() (+14 more)

### Community 18 - "Community 18"
Cohesion: 0.11
Nodes (10): RedirectResponse, Request, UserController, User, Authenticatable, CanResetPassword, CanResetPasswordContract, HasFactory (+2 more)

### Community 19 - "Community 19"
Cohesion: 0.07
Nodes (45): activeForPoint(), addBlock(), addLineDeco(), applyChanges(), balanced(), baseIndent(), baseIndentFor(), blank() (+37 more)

### Community 20 - "Community 20"
Cohesion: 0.03
Nodes (82): ac(), ah(), apply(), bg(), bl(), $c(), cc(), chartOptionScopes() (+74 more)

### Community 21 - "Community 21"
Cohesion: 0.09
Nodes (7): RedirectResponse, Request, SettingsController, AppErrorLog, BelongsTo, PermissionService, GeneralSettings

### Community 22 - "Community 22"
Cohesion: 0.07
Nodes (18): AssociateController, RedirectResponse, Request, View, ConceptController, RedirectResponse, Request, View (+10 more)

### Community 23 - "Community 23"
Cohesion: 0.05
Nodes (72): afterAutoSkip(), applyStack(), ar(), aspectRatio(), bi(), buildLookupTable(), _calculateBarIndexPixels(), _calculateBarValuePixels() (+64 more)

### Community 24 - "Community 24"
Cohesion: 0.05
Nodes (54): addChanges(), apply(), bX(), changes(), checkAsyncSchedule(), compose(), createParse(), decompose() (+46 more)

### Community 25 - "Community 25"
Cohesion: 0.05
Nodes (55): Fl(), S$(), am(), Ap(), be(), bi(), c(), clickPercent() (+47 more)

### Community 26 - "Community 26"
Cohesion: 0.08
Nodes (53): $(), oh(), adjustHitBoxes(), afterDraw(), At(), bi(), bo(), clear() (+45 more)

### Community 27 - "Community 27"
Cohesion: 0.08
Nodes (33): applyDisabledState(), b(), be(), Cn(), D(), disable(), _e(), en() (+25 more)

### Community 28 - "Community 28"
Cohesion: 0.05
Nodes (65): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate(), Ba() (+57 more)

### Community 29 - "Community 29"
Cohesion: 0.06
Nodes (46): afterDatasetsUpdate(), buildOrUpdateControllers(), _d(), _destroyDatasetMeta(), ef(), _f(), generateLabels(), getDatasetMeta() (+38 more)

### Community 30 - "Community 30"
Cohesion: 0.06
Nodes (47): active(), bd(), bidiSpans(), checkHover(), coordsAt(), coordsAtPos(), coordsIn(), coordsInWidget() (+39 more)

### Community 31 - "Community 31"
Cohesion: 0.05
Nodes (6): PdfDocumentHelper, Model, RedirectResponse, Request, Request, UploadHelper

### Community 32 - "Community 32"
Cohesion: 0.06
Nodes (43): apply(), as(), At(), close(), closeQuietly(), co(), destroy(), Do() (+35 more)

### Community 33 - "Community 33"
Cohesion: 0.06
Nodes (54): addInner(), addOptions(), addPasteRules(), ao(), chain(), check(), checkAttrs(), children() (+46 more)

### Community 34 - "Community 34"
Cohesion: 0.06
Nodes (92): _a(), addCommands(), append(), at(), ba(), Bh(), bn(), bx() (+84 more)

### Community 35 - "Community 35"
Cohesion: 0.07
Nodes (44): addElement(), addGaps(), addLeafElement(), addNode(), ATXHeading(), buildDeco(), char(), complete() (+36 more)

### Community 36 - "Community 36"
Cohesion: 0.07
Nodes (37): Ag(), applyTransaction(), asSingle(), BO(), bu(), build(), Ch(), dynamicSlot() (+29 more)

### Community 37 - "Community 37"
Cohesion: 0.09
Nodes (65): _(), ae(), areRecordsSelected(), areRecordsToggleable(), be(), C(), canSelectAllRecords(), Ce() (+57 more)

### Community 38 - "Community 38"
Cohesion: 0.05
Nodes (29): actions(), button(), close(), configureAnimations(), configureTransitions(), constructor(), danger(), dispatch() (+21 more)

### Community 39 - "Community 39"
Cohesion: 0.20
Nodes (35): _(), b(), $c(), ca(), D(), E(), g(), H() (+27 more)

### Community 40 - "Community 40"
Cohesion: 0.05
Nodes (9): Rd(), Aa(), Bi(), Bn(), ji(), Ri(), te(), Xc() (+1 more)

### Community 41 - "Community 41"
Cohesion: 0.09
Nodes (31): addActive(), Ar(), chunkEnd(), compute(), create(), desc(), Ds(), filter() (+23 more)

### Community 42 - "Community 42"
Cohesion: 0.07
Nodes (41): _a(), aa(), add(), alpha(), bl(), br(), ca(), ci() (+33 more)

### Community 43 - "Community 43"
Cohesion: 0.06
Nodes (43): y(), average(), dataset(), ec(), En(), first(), fl(), Ge() (+35 more)

### Community 44 - "Community 44"
Cohesion: 0.09
Nodes (40): We(), Ae(), ar(), Be(), Bt(), De(), _e(), Ee() (+32 more)

### Community 45 - "Community 45"
Cohesion: 0.07
Nodes (42): add(), At(), cf(), _computeLabelSizes(), createResolver(), da(), datasetElementScopeKeys(), de() (+34 more)

### Community 46 - "Community 46"
Cohesion: 0.07
Nodes (76): aS(), b1(), balance(), bS(), combine(), compare(), createDeco(), fromJSON() (+68 more)

### Community 47 - "Community 47"
Cohesion: 0.13
Nodes (69): _freeze(), at(), Be(), cd(), Cr(), Ct(), de(), dr() (+61 more)

### Community 48 - "Community 48"
Cohesion: 0.08
Nodes (37): Bg(), charCategorizer(), cs(), di(), f1(), fe(), getChild(), getChildren() (+29 more)

### Community 49 - "Community 49"
Cohesion: 0.18
Nodes (26): closeDropdown(), constructor(), createOptionElement(), destroy(), filterOptions(), focusNextOption(), focusPreviousOption(), getVisibleOptions() (+18 more)

### Community 50 - "Community 50"
Cohesion: 0.06
Nodes (54): _0(), addActions(), advance(), advanceFully(), advanceStack(), allActions(), AZ(), break() (+46 more)

### Community 51 - "Community 51"
Cohesion: 0.11
Nodes (21): active(), _animateOptions(), average(), _createAnimations(), dataset(), dh(), ee(), eh() (+13 more)

### Community 52 - "Community 52"
Cohesion: 0.16
Nodes (14): lm(), bc(), beforeLayout(), fc(), gc(), jo(), mc(), $o() (+6 more)

### Community 53 - "Community 53"
Cohesion: 0.18
Nodes (14): Hc(), ac(), cs(), Es(), lo(), ls(), nc(), path() (+6 more)

### Community 54 - "Community 54"
Cohesion: 0.07
Nodes (51): afterAutoSkip(), applyStack(), Ar(), buildLookupTable(), _calculateBarIndexPixels(), _calculateBarValuePixels(), _calculatePadding(), cc() (+43 more)

### Community 55 - "Community 55"
Cohesion: 0.05
Nodes (21): BrandSettingController, RedirectResponse, View, DashboardController, View, Request, UserPreferenceController, ForgotPasswordController (+13 more)

### Community 56 - "Community 56"
Cohesion: 0.08
Nodes (54): Ae(), Aw(), Bf(), ci(), coordsAtPos(), Ct(), cw(), Df() (+46 more)

### Community 57 - "Community 57"
Cohesion: 0.06
Nodes (44): afterDatasetsUpdate(), Ao(), beforeDatasetDraw(), beforeDatasetsDraw(), beforeDraw(), buildOrUpdateControllers(), co(), _destroyDatasetMeta() (+36 more)

### Community 58 - "Community 58"
Cohesion: 0.06
Nodes (50): an(), buildOrUpdateScales(), ch(), D(), dc(), determineDataLimits(), dh(), diff() (+42 more)

### Community 59 - "Community 59"
Cohesion: 0.15
Nodes (36): ai(), bn(), ci(), ct(), di(), Dn(), Dt(), Et() (+28 more)

### Community 60 - "Community 60"
Cohesion: 0.09
Nodes (31): addSelection(), at(), be(), boundChange(), commit(), comparePoint(), compareRange(), eq() (+23 more)

### Community 61 - "Community 61"
Cohesion: 0.09
Nodes (17): a(), ar(), at(), cr(), d(), f(), H(), ji() (+9 more)

### Community 62 - "Community 62"
Cohesion: 0.17
Nodes (32): _a(), aa(), ba(), br(), Bt(), ct(), Fa(), Fi() (+24 more)

### Community 63 - "Community 63"
Cohesion: 0.05
Nodes (55): Image(), alpha(), Bc(), Bo(), color(), cs(), darken(), desaturate() (+47 more)

### Community 64 - "Community 64"
Cohesion: 0.18
Nodes (31): $(), ai(), c(), ca(), Dn(), E(), ei(), f() (+23 more)

### Community 65 - "Community 65"
Cohesion: 0.06
Nodes (45): cO(), compositionend(), Dh(), du(), e$(), f0(), Fg(), findWidget() (+37 more)

### Community 66 - "Community 66"
Cohesion: 0.06
Nodes (40): aa(), addEventListener(), an(), au(), beforeDatasetsDraw(), beforeDraw(), bindEvents(), bindResponsiveEvents() (+32 more)

### Community 67 - "Community 67"
Cohesion: 0.07
Nodes (64): an(), On(), Vo(), Dl(), Il(), Ac(), ad(), Ae() (+56 more)

### Community 68 - "Community 68"
Cohesion: 0.14
Nodes (8): BackupController, RedirectResponse, Request, BelongsTo, SystemBackup, BackupService, StreamedResponse, UploadedFile

### Community 69 - "Community 69"
Cohesion: 0.16
Nodes (29): ae(), B(), cr(), de(), dt(), Ee(), fr(), Ge() (+21 more)

### Community 70 - "Community 70"
Cohesion: 0.11
Nodes (24): addStoredMark(), apply(), applyInner(), applyTransaction(), buildProps(), can(), commands(), createCan() (+16 more)

### Community 71 - "Community 71"
Cohesion: 0.07
Nodes (45): Bf(), bt(), buildTicks(), calculateCircumference(), calculateLabelRotation(), _calculatePadding(), ci(), _circumference() (+37 more)

### Community 72 - "Community 72"
Cohesion: 0.07
Nodes (46): bh(), buildTicks(), calculateLabelRotation(), _computeAngle(), _computeLabelItems(), _computeLabelSizes(), computeTickLimit(), Do() (+38 more)

### Community 73 - "Community 73"
Cohesion: 0.11
Nodes (27): AQ(), between(), Cg(), DQ(), dr(), findIndex(), Fn(), forRange() (+19 more)

### Community 74 - "Community 74"
Cohesion: 0.22
Nodes (8): ActivityLogController, RedirectResponse, Request, View, ActivityLog, BelongsTo, Builder, MorphTo

### Community 75 - "Community 75"
Cohesion: 0.09
Nodes (21): dependencies, alpinejs, flowbite, flowbite-datepicker, @fullcalendar/core, @fullcalendar/daygrid, @fullcalendar/interaction, @tailwindcss/forms (+13 more)

### Community 76 - "Community 76"
Cohesion: 0.10
Nodes (23): af(), Cm(), cn(), Er(), getAllParsedValues(), getDataTimestamps(), getMatchingVisibleMetas(), getMaximumSize() (+15 more)

### Community 77 - "Community 77"
Cohesion: 0.20
Nodes (5): LegalPageController, LegalPageDefaults, PublicHtmlSanitizer, up(), HtmlSanitizer

### Community 78 - "Community 78"
Cohesion: 0.10
Nodes (26): addMaps(), addStep(), addTransform(), al(), appendMap(), appendMapping(), appendMappingInverted(), Bb() (+18 more)

### Community 79 - "Community 79"
Cohesion: 0.13
Nodes (10): C(), close(), init(), P(), Q(), R(), setUpResizeObserver(), v() (+2 more)

### Community 80 - "hd"
Cohesion: 0.09
Nodes (25): $a(), ad(), bd(), beforeLayout(), cd(), data(), Fa(), first() (+17 more)

### Community 81 - "_each"
Cohesion: 0.11
Nodes (22): addControllers(), addElements(), addPlugins(), addScales(), buildOrUpdateElements(), _dataCheck(), _each(), ei() (+14 more)

### Community 82 - "Community 82"
Cohesion: 0.17
Nodes (5): DatabaseSeeder, EmailTemplateSeeder, ServiceTypeSeeder, Seeder, WithoutModelEvents

### Community 83 - "hd"
Cohesion: 0.13
Nodes (19): Ai(), Bg(), Ca(), fg(), hd(), Hs(), $nodes(), Or() (+11 more)

### Community 84 - "Community 84"
Cohesion: 0.09
Nodes (34): ba(), Bt(), Ce(), createResolver(), _e(), El(), ga(), getPadding() (+26 more)

### Community 85 - "Community 85"
Cohesion: 0.21
Nodes (19): Ae(), bi(), Bt(), Ce(), De(), ei(), fn(), ht() (+11 more)

### Community 86 - "Community 86"
Cohesion: 0.06
Nodes (57): addAll(), addDOM(), addElement(), addElementByRule(), addTextNode(), addToSet(), ag(), allowedMarks() (+49 more)

### Community 87 - "Community 87"
Cohesion: 0.23
Nodes (14): da(), ef(), fa(), Gr(), Kr(), ll(), no(), pa() (+6 more)

### Community 88 - "Community 88"
Cohesion: 0.26
Nodes (14): addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), createBadgeElement(), createRemoveButton(), deferPositionDropdown(), getLabelForSingleSelection(), getLabelsForMultipleSelection() (+6 more)

### Community 89 - "Community 89"
Cohesion: 0.19
Nodes (6): LoginController, Request, LoginIpLockout, LoginLockoutService, Request, CarbonInterface

### Community 91 - "Community 91"
Cohesion: 0.14
Nodes (4): [_](), [g](), style(), update()

### Community 92 - "Community 92"
Cohesion: 0.17
Nodes (16): ar(), Cn(), Da(), En(), fe(), ir(), J(), ne() (+8 more)

### Community 93 - "getContext"
Cohesion: 0.24
Nodes (12): acquireContext(), Dr(), Ee(), getContext(), il(), kl(), oh(), or() (+4 more)

### Community 94 - "Community 94"
Cohesion: 0.08
Nodes (11): ConceptPrice, BelongsTo, EmailLog, BelongsTo, EmailTemplate, self, self, UserFactory (+3 more)

### Community 95 - "Community 95"
Cohesion: 0.20
Nodes (14): active(), _animateOptions(), cancel(), _createAnimations(), _createDescriptors(), _descriptors(), _notify(), _notifyStateChanges() (+6 more)

### Community 96 - "Community 96"
Cohesion: 0.40
Nodes (4): CheckModulePermission, Closure, Request, Response

### Community 97 - "Community 97"
Cohesion: 0.25
Nodes (9): zu(), ch(), Dd(), describe(), ds(), qu(), route(), td() (+1 more)

### Community 98 - "Community 98"
Cohesion: 0.36
Nodes (3): AppErrorLogService, GitWorkingCopyService, Throwable

### Community 99 - "Community 99"
Cohesion: 0.17
Nodes (12): require, bacon/bacon-qr-code, barryvdh/laravel-dompdf, laravel/framework, laravel/tinker, maatwebsite/excel, owen-it/laravel-auditing, php (+4 more)

### Community 100 - "Community 100"
Cohesion: 0.22
Nodes (11): Be(), ei(), Fe(), He(), le(), Mt(), ni(), r() (+3 more)

### Community 101 - "Community 101"
Cohesion: 0.20
Nodes (11): Ce(), De(), Ht(), Ie(), ii(), oi(), Re(), t() (+3 more)

### Community 102 - "Community 102"
Cohesion: 0.14
Nodes (4): AppServiceProvider, EmailTemplateService, ServiceProvider, Settings

### Community 103 - "Community 103"
Cohesion: 0.28
Nodes (3): Calendar, StatsCard, Component

### Community 104 - "Community 104"
Cohesion: 0.28
Nodes (4): BaseTestCase, ExampleTest, TestCase, ExampleTest

### Community 105 - "Community 105"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 106 - "Community 106"
Cohesion: 0.22
Nodes (9): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, platform, preferred-install, sort-packages (+1 more)

### Community 107 - "Community 107"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 108 - "Community 108"
Cohesion: 0.31
Nodes (8): _(), b(), di(), e(), g(), i(), P(), xr()

### Community 110 - "Community 110"
Cohesion: 0.31
Nodes (9): bo(), hs(), kn(), Mo(), qe(), sn(), St(), Tr() (+1 more)

### Community 111 - "Community 111"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 112 - "Community 112"
Cohesion: 0.33
Nodes (5): 1. Actualizar base de datos, 2. Enlace de almacenamiento (solo la primera vez o si se borró), 3. Limpiar cachés, Instructivo: Actualización en el servidor (RAMS), Resumen rápido (copiar y pegar)

### Community 113 - "Community 113"
Cohesion: 0.70
Nodes (4): checkPermission(), RedirectResponse, redirectIfNoPermission(), requirePermission()

### Community 114 - "Community 114"
Cohesion: 0.38
Nodes (7): g(), Hn(), $i(), Rt(), Ut(), Z(), ze()

### Community 115 - "Community 115"
Cohesion: 0.53
Nodes (4): EnsureClientCanAccessPortal, Closure, Request, Response

### Community 116 - "Community 116"
Cohesion: 0.53
Nodes (4): EnsureTwoFactorLoginPending, Closure, Request, Response

### Community 117 - "Community 117"
Cohesion: 0.53
Nodes (4): EnsureUserIsClient, Closure, Request, Response

### Community 118 - "Community 118"
Cohesion: 0.53
Nodes (4): EnsureUserIsNotClient, Closure, Request, Response

### Community 119 - "Community 119"
Cohesion: 0.53
Nodes (4): Closure, Request, Response, PreventAdminCache

### Community 120 - "Community 120"
Cohesion: 0.53
Nodes (4): Closure, Request, Response, SecurityHeaders

### Community 121 - "Community 121"
Cohesion: 0.73
Nodes (5): closeModal(), generateModalId(), init(), openModal(), syncActionModals()

### Community 123 - "Community 123"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 127 - "Community 127"
Cohesion: 0.50
Nodes (4): Dt(), ir(), nr(), rt()

### Community 135 - "Community 135"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 136 - "Community 136"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **99 isolated node(s):** `$schema`, `name`, `type`, `description`, `keywords` (+94 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **29 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `u()` connect `Community 1` to `Community 0`, `Community 3`, `Community 4`, `Community 6`, `Community 8`, `Community 9`, `Community 10`, `Community 12`, `Community 14`, `Community 15`, `Community 19`, `Community 20`, `Community 23`, `Community 26`, `Community 27`, `Community 28`, `Community 29`, `Community 30`, `Community 32`, `Community 33`, `Community 34`, `Community 37`, `Community 39`, `Community 41`, `Community 43`, `Community 44`, `Community 45`, `Community 46`, `Community 47`, `Community 50`, `Community 51`, `Community 53`, `Community 54`, `Community 58`, `Community 62`, `Community 64`, `Community 66`, `Community 67`, `Community 69`, `Community 70`, `Community 71`, `Community 72`, `Community 73`, `Community 78`, `Community 79`, `hd`, `Community 84`, `Community 92`, `Community 108`, `Community 114`?**
  _High betweenness centrality (0.133) - this node is a cross-community bridge._
- **Why does `update()` connect `Community 3` to `Community 0`, `Community 1`, `Community 6`, `Community 9`, `Community 11`, `Community 15`, `Community 19`, `Community 24`, `Community 30`, `Community 33`, `Community 35`, `Community 36`, `Community 39`, `Community 40`, `Community 41`, `Community 46`, `Community 48`, `Community 50`, `Community 60`, `Community 65`, `Community 67`, `Community 73`, `Community 87`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **Why does `t()` connect `Community 46` to `Community 0`, `Community 1`, `Community 3`, `Community 4`, `Community 6`, `Community 8`, `Community 9`, `Community 11`, `Community 15`, `Community 24`, `Community 30`, `Community 33`, `Community 34`, `Community 35`, `Community 36`, `Community 39`, `Community 40`, `Community 41`, `Community 47`, `Community 48`, `Community 50`, `Community 56`, `Community 60`, `Community 65`, `Community 67`, `Community 70`, `Community 86`, `Community 87`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Are the 189 inferred relationships involving `r()` (e.g. with `aS()` and `balance()`) actually correct?**
  _`r()` has 189 INFERRED edges - model-reasoned connections that need verification._
- **Are the 167 inferred relationships involving `i()` (e.g. with `add()` and `addElement()`) actually correct?**
  _`i()` has 167 INFERRED edges - model-reasoned connections that need verification._
- **Are the 158 inferred relationships involving `t()` (e.g. with `add()` and `addCompletions()`) actually correct?**
  _`t()` has 158 INFERRED edges - model-reasoned connections that need verification._
- **Are the 144 inferred relationships involving `a()` (e.g. with `ac()` and `addElement()`) actually correct?**
  _`a()` has 144 INFERRED edges - model-reasoned connections that need verification._