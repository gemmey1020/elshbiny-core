<?php

namespace App\Providers\Filament;

use App\Http\Middleware\OmegaPassGate;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OmegaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('omega')
            ->path('omega')
            ->login()
            ->brandName('Elshbiny Omega')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => '#ffffff',
                'gray' => Color::Zinc,
                'success' => '#10b981',
                'danger' => '#ef4444',
                'warning' => '#f59e0b',
            ])
            ->font('Inter')
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString('
                    <style>
                        body, .fi-layout {
                            background-color: #0f111a !important;
                        }
                        .fi-sidebar {
                            background: rgba(15, 17, 26, 0.75) !important;
                            backdrop-filter: blur(16px) !important;
                            border-inline-end: 1px solid rgba(255, 255, 255, 0.08) !important;
                        }
                        .fi-section, 
                        .fi-fo-card, 
                        .fi-g-card, 
                        .fi-stats-overview-stat,
                        .fi-widget {
                            background: rgba(255, 255, 255, 0.02) !important;
                            backdrop-filter: blur(12px) !important;
                            border: 1px solid rgba(255, 255, 255, 0.08) !important;
                            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.25) !important;
                            border-radius: 8px !important;
                        }
                        .fi-sidebar-item-active a {
                            background: rgba(255, 255, 255, 0.06) !important;
                            border: 1px solid rgba(255, 255, 255, 0.1) !important;
                        }
                        .fi-input-wrp {
                            background: rgba(255, 255, 255, 0.02) !important;
                            border: 1px solid rgba(255, 255, 255, 0.08) !important;
                            backdrop-filter: blur(8px) !important;
                        }
                        .fi-btn-color-primary,
                        .fi-btn[type="submit"],
                        .fi-ac-btn-action {
                            background-color: #ffffff !important;
                            color: #0f111a !important;
                            font-weight: 600 !important;
                            transition: all 0.2s ease-in-out !important;
                        }
                        .fi-btn-color-primary:hover,
                        .fi-btn[type="submit"]:hover,
                        .fi-ac-btn-action:hover {
                            background-color: #e2e8f0 !important;
                            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3) !important;
                        }
                        
                        /* Upgraded HUD Keyframe Animations */
                        @keyframes omegaPulse {
                            0%, 100% {
                                opacity: 0.4;
                                text-shadow: 0 0 15px rgba(0, 243, 255, 0.3);
                                transform: scale(0.96);
                            }
                            50% {
                                opacity: 1;
                                text-shadow: 0 0 35px rgba(0, 243, 255, 0.8), 0 0 50px rgba(0, 243, 255, 0.4);
                                transform: scale(1.04);
                            }
                        }
                        @keyframes omegaRotate {
                            from { transform: rotate(0deg); }
                            to { transform: rotate(360deg); }
                        }
                        @keyframes omegaRotateCounter {
                            from { transform: rotate(360deg); }
                            to { transform: rotate(0deg); }
                        }
                        @keyframes omegaLaserScan {
                            0%, 100% {
                                opacity: 0.3;
                                width: 80px;
                            }
                            50% {
                                opacity: 1;
                                width: 140px;
                            }
                        }
                        .omega-fade-out {
                            opacity: 0 !important;
                            visibility: hidden !important;
                        }
                    </style>
                '),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_START,
                fn (): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString('
                    <div id="omega-preloader" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: #0f111a;
                        z-index: 99999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.6s cubic-bezier(0.4, 0, 0.2, 1);
                    ">
                        <!-- Scanline texture overlay -->
                        <div style="
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.15) 50%);
                            background-size: 100% 4px;
                            z-index: 1;
                            pointer-events: none;
                            opacity: 0.4;
                        "></div>

                        <div style="text-align: center; display: flex; flex-direction: column; align-items: center; z-index: 2;">
                            <!-- Rotating HUD dials wrapper -->
                            <div style="position: relative; width: 220px; height: 220px; display: flex; align-items: center; justify-content: center; margin-bottom: 25px;">
                                <!-- Outer Dial -->
                                <div style="
                                    position: absolute;
                                    width: 220px;
                                    height: 220px;
                                    border: 2px dashed rgba(0, 243, 255, 0.12);
                                    border-radius: 50%;
                                    animation: omegaRotate 16s linear infinite;
                                "></div>
                                <!-- Inner Dial -->
                                <div style="
                                    position: absolute;
                                    width: 180px;
                                    height: 180px;
                                    border: 1px dashed rgba(0, 243, 255, 0.25);
                                    border-radius: 50%;
                                    animation: omegaRotateCounter 10s linear infinite;
                                "></div>
                                <!-- Center Ω symbol -->
                                <div style="
                                    font-size: 85px;
                                    font-weight: 300;
                                    color: #00f3ff;
                                    text-shadow: 0 0 25px rgba(0, 243, 255, 0.65);
                                    animation: omegaPulse 1.8s infinite ease-in-out;
                                    user-select: none;
                                    z-index: 3;
                                ">Ω</div>
                            </div>

                            <!-- Metallic Title -->
                            <div style="
                                color: #ffffff;
                                font-family: \'Inter\', sans-serif;
                                font-size: 11px;
                                font-weight: 600;
                                letter-spacing: 0.5em;
                                text-transform: uppercase;
                                margin-inline-start: 0.5em;
                                opacity: 0.75;
                                user-select: none;
                            ">Omega Sovereign</div>

                            <!-- Laser scanline below text -->
                            <div style="
                                height: 1px;
                                background: linear-gradient(90deg, transparent, #00f3ff, transparent);
                                box-shadow: 0 0 8px #00f3ff;
                                margin-top: 15px;
                                animation: omegaLaserScan 2s infinite ease-in-out;
                            "></div>
                        </div>
                    </div>
                '),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString('
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const preloader = document.getElementById("omega-preloader");
                            if (preloader) {
                                setTimeout(function() {
                                    preloader.classList.add("omega-fade-out");
                                    setTimeout(function() {
                                        preloader.remove();
                                    }, 600);
                                }, 900);
                            }
                        });
                    </script>
                '),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\OmegaStatsOverview::class,
                \App\Filament\Widgets\OmegaQuickActions::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                OmegaPassGate::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
