# Graph Report - INVOICE-CC-ACCIONCOL  (2026-07-09)

## Corpus Check
- 315 files · ~296,177 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 6066 nodes · 18489 edges · 323 communities (293 shown, 30 thin omitted)
- Extraction: 87% EXTRACTED · 13% INFERRED · 0% AMBIGUOUS · INFERRED: 2315 edges (avg confidence: 0.72)
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
- Community 82
- Community 84
- Community 85
- Community 86
- Community 88
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
- Community 111
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
- `up()` --calls--> `LegalPageDefaults`  [INFERRED]
  database/settings/2026_04_01_000000_add_legal_html_to_general_settings.php → app/Support/LegalPageDefaults.php
- `up()` --calls--> `RolePermission`  [INFERRED]
  database/migrations/2026_03_25_120000_seed_role_permissions_matrix_defaults.php → app/Models/RolePermission.php
- `up()` --calls--> `RolePermission`  [INFERRED]
  database/migrations/2026_07_10_000002_seed_invoice_module_permissions.php → app/Models/RolePermission.php
- `up()` --calls--> `Setting`  [EXTRACTED]
  database/migrations/2026_07_10_000002_seed_invoice_module_permissions.php → app/Models/Setting.php
- `up()` --calls--> `PermissionService`  [INFERRED]
  database/migrations/2026_03_25_120000_seed_role_permissions_matrix_defaults.php → app/Services/PermissionService.php

## Import Cycles
- None detected.

## Communities (323 total, 30 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.01
Nodes (101): aa(), ak(), attrs(), b1(), [b.Blockquote](), [b.ListItem](), baseTheme(), bh() (+93 more)

### Community 1 - "Community 1"
Cohesion: 0.04
Nodes (71): ad(), addGlobalAttributes(), addNode(), applyInitialSize(), bs(), constructor(), createCommandManager(), createContainer() (+63 more)

### Community 2 - "Community 2"
Cohesion: 0.01
Nodes (100): aa(), abutsStart(), addControllers(), addPlugins(), addScales(), Bh(), clear(), color() (+92 more)

### Community 3 - "Community 3"
Cohesion: 0.02
Nodes (170): accept(), active(), add(), addChunk(), addEventListener(), addInfoPane(), addInner(), addWindowListeners() (+162 more)

### Community 4 - "Community 4"
Cohesion: 0.01
Nodes (199): Aa(), Ac(), add(), addExtensions(), addHackNode(), addNodeMark(), addTextblockHacks(), am() (+191 more)

### Community 5 - "Community 5"
Cohesion: 0.02
Nodes (94): addControllers(), addElements(), addPlugins(), addScales(), as(), br(), Ce(), _checkEventBindings() (+86 more)

### Community 6 - "Community 6"
Cohesion: 0.05
Nodes (109): addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), Ae(), ai(), applyDisabledState(), be(), bn() (+101 more)

### Community 7 - "Community 7"
Cohesion: 0.09
Nodes (13): BrandSettingController, RedirectResponse, View, InvoiceMail, Setting, AppServiceProvider, Content, Envelope (+5 more)

### Community 8 - "Community 8"
Cohesion: 0.06
Nodes (122): addKeyboardShortcuts(), after(), An(), Ar(), bc(), bd(), before(), Bi() (+114 more)

### Community 9 - "Community 9"
Cohesion: 0.04
Nodes (105): addCompletion(), addCompletions(), addNamespace(), addNamespaceObject(), ah(), Ao(), AP(), atLastNode() (+97 more)

### Community 10 - "Community 10"
Cohesion: 0.04
Nodes (107): acquireContext(), adjustHitBoxes(), afterDraw(), Bf(), bn(), bs(), bt(), bu() (+99 more)

### Community 11 - "Community 11"
Cohesion: 0.03
Nodes (173): Yl(), _a(), Ac(), ad(), Ae(), af(), ai(), al() (+165 more)

### Community 12 - "Community 12"
Cohesion: 0.04
Nodes (90): vm(), after(), ag(), Am(), as(), before(), bg(), bm() (+82 more)

### Community 13 - "Community 13"
Cohesion: 0.12
Nodes (9): LogAdminMutationActivity, Closure, Request, Response, ActivityLogService, Model, Request, Response (+1 more)

### Community 14 - "Community 14"
Cohesion: 0.05
Nodes (67): ae(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterDraw(), afterFit(), afterSetDimensions(), afterTickToLabelConversion() (+59 more)

### Community 15 - "Community 15"
Cohesion: 0.05
Nodes (60): addRange(), Bi(), blockPosCovered(), blockTiles(), blur(), clear(), cn(), coordsAt() (+52 more)

### Community 16 - "Community 16"
Cohesion: 0.09
Nodes (11): PermissionController, RedirectResponse, Request, BelongsTo, RoleHierarchy, BelongsTo, RolePermission, PermissionService (+3 more)

### Community 17 - "Community 17"
Cohesion: 0.11
Nodes (32): addBlockWidget(), addBreak(), addComposition(), addDelimiter(), addInlineWidget(), addLine(), addLineStart(), addLineStartIfNotCovered() (+24 more)

### Community 18 - "Community 18"
Cohesion: 0.13
Nodes (10): RedirectResponse, Request, UserController, User, Authenticatable, CanResetPassword, CanResetPasswordContract, HasFactory (+2 more)

### Community 19 - "Community 19"
Cohesion: 0.04
Nodes (73): ad(), addBlock(), addChanges(), addLineDeco(), applyChanges(), balanced(), baseIndent(), baseIndentFor() (+65 more)

### Community 20 - "Community 20"
Cohesion: 0.04
Nodes (71): Image(), ac(), ah(), $c(), cc(), clone(), create(), dtFormatter() (+63 more)

### Community 21 - "Community 21"
Cohesion: 0.12
Nodes (6): RedirectResponse, Request, SettingsController, AppErrorLog, BelongsTo, GeneralSettings

### Community 22 - "Community 22"
Cohesion: 0.04
Nodes (28): AssociateController, RedirectResponse, Request, View, ConceptController, RedirectResponse, Request, View (+20 more)

### Community 23 - "Community 23"
Cohesion: 0.04
Nodes (88): addEventListener(), afterAutoSkip(), applyStack(), ar(), aspectRatio(), At(), au(), bi() (+80 more)

### Community 24 - "Community 24"
Cohesion: 0.09
Nodes (28): addEventListener(), al(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), bs(), _cachedScopes(), cl() (+20 more)

### Community 25 - "Community 25"
Cohesion: 0.05
Nodes (55): Fl(), S$(), am(), Ap(), be(), bi(), c(), clickPercent() (+47 more)

### Community 26 - "Community 26"
Cohesion: 0.05
Nodes (77): $(), Hc(), adjustHitBoxes(), At(), bi(), bo(), _calculatePadding(), clear() (+69 more)

### Community 27 - "Community 27"
Cohesion: 0.08
Nodes (33): applyDisabledState(), b(), be(), Cn(), D(), disable(), _e(), en() (+25 more)

### Community 28 - "Community 28"
Cohesion: 0.04
Nodes (103): addBox(), addElements(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion() (+95 more)

### Community 29 - "Community 29"
Cohesion: 0.11
Nodes (26): afterDatasetsUpdate(), ef(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth(), getStyle(), gs() (+18 more)

### Community 30 - "Community 30"
Cohesion: 0.05
Nodes (52): AZ(), bidiSpans(), checkHover(), coordsAtPos(), dispatch(), dP(), Ed(), Ef() (+44 more)

### Community 31 - "Community 31"
Cohesion: 0.05
Nodes (6): PdfDocumentHelper, Model, RedirectResponse, Request, Request, UploadHelper

### Community 32 - "Community 32"
Cohesion: 0.06
Nodes (40): ar(), B(), bo(), close(), closeQuietly(), co(), fe(), Ga() (+32 more)

### Community 33 - "Community 33"
Cohesion: 0.10
Nodes (27): addAttributes(), addOptions(), Bg(), closest(), cr(), Dy(), ef(), ew() (+19 more)

### Community 34 - "Community 34"
Cohesion: 0.05
Nodes (131): _a(), addCommands(), Ai(), allowedMarks(), at(), ax(), Bh(), bn() (+123 more)

### Community 35 - "Community 35"
Cohesion: 0.05
Nodes (70): addChild(), addGaps(), addLeafElement(), addNode(), advance(), ATXHeading(), break(), char() (+62 more)

### Community 36 - "Community 36"
Cohesion: 0.12
Nodes (22): acceptToken(), allows(), $d(), eh(), gv(), Hc(), Jm(), nh() (+14 more)

### Community 37 - "Community 37"
Cohesion: 0.09
Nodes (65): _(), ae(), areRecordsSelected(), areRecordsToggleable(), be(), C(), canSelectAllRecords(), Ce() (+57 more)

### Community 38 - "Community 38"
Cohesion: 0.05
Nodes (29): actions(), button(), close(), configureAnimations(), configureTransitions(), constructor(), danger(), dispatch() (+21 more)

### Community 39 - "Community 39"
Cohesion: 0.27
Nodes (31): _(), b(), $c(), ca(), D(), E(), g(), He() (+23 more)

### Community 40 - "Community 40"
Cohesion: 0.03
Nodes (36): Rd(), Aa(), Bi(), Bn(), cf(), da(), ef(), fa() (+28 more)

### Community 41 - "Community 41"
Cohesion: 0.05
Nodes (64): activeForPoint(), addActive(), Ag(), Ar(), at(), be(), BO(), boundChange() (+56 more)

### Community 42 - "Community 42"
Cohesion: 0.07
Nodes (40): Gm(), _a(), aa(), add(), alpha(), ba(), ca(), ci() (+32 more)

### Community 43 - "Community 43"
Cohesion: 0.07
Nodes (43): average(), bh(), calculateLabelRotation(), dataset(), Ge(), getCenterPoint(), getIndexAngle(), _getLegendItemAt() (+35 more)

### Community 44 - "Community 44"
Cohesion: 0.09
Nodes (39): We(), Ae(), ar(), Be(), Bt(), De(), _e(), Ee() (+31 more)

### Community 45 - "Community 45"
Cohesion: 0.04
Nodes (81): Kn(), readOnly(), zu(), $a(), ad(), add(), ai(), apply() (+73 more)

### Community 46 - "Community 46"
Cohesion: 0.04
Nodes (144): ac(), addElement(), aS(), balance(), bS(), buildDeco(), bX(), cd() (+136 more)

### Community 47 - "Community 47"
Cohesion: 0.12
Nodes (73): define(), _freeze(), at(), Be(), cd(), Cr(), Ct(), de() (+65 more)

### Community 48 - "Community 48"
Cohesion: 0.08
Nodes (35): AQ(), Bg(), Cg(), charCategorizer(), cs(), di(), DQ(), f1() (+27 more)

### Community 49 - "Community 49"
Cohesion: 0.18
Nodes (26): closeDropdown(), constructor(), createOptionElement(), destroy(), filterOptions(), focusNextOption(), focusPreviousOption(), getVisibleOptions() (+18 more)

### Community 50 - "Community 50"
Cohesion: 0.10
Nodes (38): _0(), addActions(), advanceFully(), advanceStack(), allActions(), canShift(), close(), deadEnd() (+30 more)

### Community 51 - "Community 51"
Cohesion: 0.07
Nodes (33): active(), _animateOptions(), average(), _createAnimations(), dataset(), dh(), ee(), eh() (+25 more)

### Community 52 - "Community 52"
Cohesion: 0.13
Nodes (17): bl(), lm(), Ot(), Tt(), bc(), beforeLayout(), fc(), gc() (+9 more)

### Community 53 - "Community 53"
Cohesion: 0.11
Nodes (31): Fo(), Po(), Qo(), Vo(), zn(), Dl(), Il(), A0() (+23 more)

### Community 54 - "Community 54"
Cohesion: 0.09
Nodes (38): applyStack(), _calculateBarIndexPixels(), _calculateBarValuePixels(), calculateCircumference(), _circumference(), countVisibleElements(), _createItems(), datasetAnimationScopeKeys() (+30 more)

### Community 55 - "Community 55"
Cohesion: 0.06
Nodes (18): DashboardController, View, Request, UserPreferenceController, ForgotPasswordController, Request, LoginController, Request (+10 more)

### Community 56 - "Community 56"
Cohesion: 0.08
Nodes (57): updateEditContextFormatting(), Ae(), Aw(), ci(), Ct(), cw(), Df(), di() (+49 more)

### Community 57 - "Community 57"
Cohesion: 0.06
Nodes (44): afterDatasetsUpdate(), Ao(), beforeDatasetDraw(), beforeDatasetsDraw(), beforeDraw(), buildOrUpdateControllers(), co(), _destroyDatasetMeta() (+36 more)

### Community 58 - "Community 58"
Cohesion: 0.05
Nodes (64): afterAutoSkip(), an(), Ar(), buildLookupTable(), buildOrUpdateElements(), buildOrUpdateScales(), buildTicks(), ch() (+56 more)

### Community 59 - "Community 59"
Cohesion: 0.15
Nodes (36): ai(), bn(), ci(), ct(), di(), Dn(), Dt(), Et() (+28 more)

### Community 60 - "Community 60"
Cohesion: 0.13
Nodes (19): addSelection(), after(), before(), composeDesc(), Hh(), jo(), ju(), mn() (+11 more)

### Community 61 - "Community 61"
Cohesion: 0.09
Nodes (17): a(), ar(), at(), cr(), d(), f(), H(), ji() (+9 more)

### Community 62 - "Community 62"
Cohesion: 0.17
Nodes (32): _a(), aa(), ba(), br(), Bt(), ct(), ei(), Fa() (+24 more)

### Community 63 - "Community 63"
Cohesion: 0.08
Nodes (36): alpha(), Bc(), cs(), darken(), desaturate(), explainFromTokens(), Fc(), fh() (+28 more)

### Community 64 - "Community 64"
Cohesion: 0.17
Nodes (23): ca(), Dn(), En(), fn(), Ii(), jt(), Li(), mr() (+15 more)

### Community 65 - "Community 65"
Cohesion: 0.07
Nodes (37): compositionend(), Dh(), du(), Fg(), findWidget(), gapSize(), gd(), hg() (+29 more)

### Community 66 - "Community 66"
Cohesion: 0.12
Nodes (22): an(), beforeDatasetsDraw(), beforeDraw(), Ca(), _drawDatasets(), fn(), _getSortedDatasetMetas(), getSortedVisibleDatasetMetas() (+14 more)

### Community 67 - "Community 67"
Cohesion: 0.15
Nodes (18): addToSet(), an(), bd(), childString(), clearDelayedAndroidKey(), delayAndroidKey(), flushIOSKey(), ii() (+10 more)

### Community 68 - "Community 68"
Cohesion: 0.14
Nodes (8): BackupController, RedirectResponse, Request, BelongsTo, SystemBackup, BackupService, StreamedResponse, UploadedFile

### Community 69 - "Community 69"
Cohesion: 0.18
Nodes (26): ae(), cr(), de(), dt(), Ee(), fr(), Ge(), Gt() (+18 more)

### Community 70 - "Community 70"
Cohesion: 0.13
Nodes (21): accepts(), E0(), endIndex(), getObj(), go(), H0(), hasProtocol(), M0() (+13 more)

### Community 71 - "Community 71"
Cohesion: 0.15
Nodes (18): da(), defaultType(), done(), eat(), edge(), err(), fp(), hasRequiredAttrs() (+10 more)

### Community 72 - "Community 72"
Cohesion: 0.24
Nodes (5): self, self, UserFactory, Factory, static

### Community 73 - "Community 73"
Cohesion: 0.27
Nodes (10): apply(), as(), At(), it(), Ka(), Mt(), _o(), rr() (+2 more)

### Community 74 - "Community 74"
Cohesion: 0.22
Nodes (8): ActivityLogController, RedirectResponse, Request, View, ActivityLog, BelongsTo, Builder, MorphTo

### Community 75 - "Community 75"
Cohesion: 0.09
Nodes (21): dependencies, alpinejs, flowbite, flowbite-datepicker, @fullcalendar/core, @fullcalendar/daygrid, @fullcalendar/interaction, @tailwindcss/forms (+13 more)

### Community 76 - "Community 76"
Cohesion: 0.06
Nodes (51): af(), al(), Ao(), cn(), co(), daysInYear(), features(), getAllParsedValues() (+43 more)

### Community 77 - "Community 77"
Cohesion: 0.20
Nodes (5): LegalPageController, LegalPageDefaults, PublicHtmlSanitizer, up(), HtmlSanitizer

### Community 78 - "Community 78"
Cohesion: 0.05
Nodes (54): addMaps(), addStep(), addTransform(), al(), append(), appendMap(), appendMapping(), appendMappingInverted() (+46 more)

### Community 79 - "Community 79"
Cohesion: 0.13
Nodes (10): C(), close(), init(), P(), Q(), R(), setUpResizeObserver(), v() (+2 more)

### Community 80 - "hd"
Cohesion: 0.33
Nodes (6): Ah(), Gh(), Gl(), posAtCoords(), wx(), Zw()

### Community 82 - "Community 82"
Cohesion: 0.17
Nodes (5): DatabaseSeeder, EmailTemplateSeeder, ServiceTypeSeeder, Seeder, WithoutModelEvents

### Community 84 - "Community 84"
Cohesion: 0.11
Nodes (25): Bt(), cc(), createResolver(), dc(), ga(), ka(), lc(), Ma() (+17 more)

### Community 85 - "Community 85"
Cohesion: 0.21
Nodes (19): Ae(), bi(), Bt(), Ce(), De(), ei(), fn(), ht() (+11 more)

### Community 86 - "Community 86"
Cohesion: 0.04
Nodes (79): Ab(), addAll(), addDOM(), addElement(), addElementByRule(), addInner(), addMark(), addProseMirrorPlugins() (+71 more)

### Community 88 - "Community 88"
Cohesion: 0.26
Nodes (14): addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), createBadgeElement(), createRemoveButton(), deferPositionDropdown(), getLabelForSingleSelection(), getLabelsForMultipleSelection() (+6 more)

### Community 91 - "Community 91"
Cohesion: 0.14
Nodes (4): [_](), [g](), style(), update()

### Community 92 - "Community 92"
Cohesion: 0.67
Nodes (4): Cn(), Da(), J(), ne()

### Community 93 - "getContext"
Cohesion: 0.11
Nodes (23): acquireContext(), bl(), data(), Dr(), Ee(), fl(), Fo(), getContext() (+15 more)

### Community 94 - "Community 94"
Cohesion: 0.08
Nodes (12): ConceptPrice, BelongsTo, EmailLog, BelongsTo, EmailTemplate, LoginIpLockout, BelongsTo, TwoFactorEmailToken (+4 more)

### Community 95 - "Community 95"
Cohesion: 0.20
Nodes (14): active(), _animateOptions(), cancel(), _createAnimations(), _createDescriptors(), _descriptors(), _notify(), _notifyStateChanges() (+6 more)

### Community 96 - "Community 96"
Cohesion: 0.40
Nodes (4): CheckModulePermission, Closure, Request, Response

### Community 97 - "Community 97"
Cohesion: 0.07
Nodes (28): hl(), Th(), top(), ch(), _d(), Dd(), describe(), ds() (+20 more)

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

### Community 111 - "Community 111"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 113 - "Community 113"
Cohesion: 0.70
Nodes (4): checkPermission(), RedirectResponse, redirectIfNoPermission(), requirePermission()

### Community 114 - "Community 114"
Cohesion: 0.14
Nodes (33): $(), ai(), c(), destroy(), Do(), E(), es(), f() (+25 more)

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
- **95 isolated node(s):** `$schema`, `name`, `type`, `description`, `keywords` (+90 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **30 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `u()` connect `Community 46` to `Community 1`, `Community 3`, `Community 5`, `Community 8`, `Community 9`, `Community 10`, `Community 11`, `Community 12`, `Community 14`, `Community 15`, `Community 17`, `Community 19`, `Community 23`, `Community 26`, `Community 27`, `Community 28`, `Community 32`, `Community 33`, `Community 34`, `Community 37`, `Community 39`, `Community 43`, `Community 44`, `Community 45`, `Community 47`, `Community 50`, `Community 51`, `Community 53`, `Community 54`, `Community 58`, `Community 62`, `Community 64`, `Community 69`, `Community 78`, `Community 79`, `Community 84`, `Community 86`, `Community 92`, `Community 97`, `Community 108`, `Community 114`?**
  _High betweenness centrality (0.142) - this node is a cross-community bridge._
- **Why does `t()` connect `Community 46` to `Community 0`, `Community 1`, `Community 3`, `Community 4`, `Community 6`, `Community 8`, `Community 9`, `Community 11`, `Community 15`, `Community 30`, `Community 33`, `Community 34`, `Community 35`, `Community 39`, `Community 40`, `Community 41`, `Community 45`, `Community 47`, `Community 50`, `Community 56`, `Community 60`, `Community 65`, `Community 67`, `Community 71`, `Community 78`, `Community 86`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **Why does `Yo()` connect `Community 53` to `Community 3`, `Community 35`, `Community 5`, `Community 9`, `Community 58`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Are the 189 inferred relationships involving `r()` (e.g. with `aS()` and `balance()`) actually correct?**
  _`r()` has 189 INFERRED edges - model-reasoned connections that need verification._
- **Are the 167 inferred relationships involving `i()` (e.g. with `add()` and `addElement()`) actually correct?**
  _`i()` has 167 INFERRED edges - model-reasoned connections that need verification._
- **Are the 158 inferred relationships involving `t()` (e.g. with `add()` and `addCompletions()`) actually correct?**
  _`t()` has 158 INFERRED edges - model-reasoned connections that need verification._
- **Are the 144 inferred relationships involving `a()` (e.g. with `ac()` and `addElement()`) actually correct?**
  _`a()` has 144 INFERRED edges - model-reasoned connections that need verification._