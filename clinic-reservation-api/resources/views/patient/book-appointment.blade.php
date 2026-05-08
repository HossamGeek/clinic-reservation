<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Book Appointment | ClinicReserve</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="booking-page" data-page="patient-booking" data-doctor-id="{{ $doctorId ?? '' }}">
        <div class="booking-shell">
            <aside class="booking-sidebar" aria-label="Provider navigation">
                <div class="sidebar-brand">
                    <strong>Clinic Management</strong>
                    <span>Provider Portal</span>
                </div>

                <button class="new-appointment-button" type="button"><span aria-hidden="true">+</span> New Appointment</button>

                <nav class="sidebar-nav">
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 6V0H18V6H10ZM0 10V0H8V10H0ZM10 18V8H18V18H10ZM0 18V12H8V18H0ZM2 8H6V2H2V8ZM12 16H16V10H12V16ZM12 4H16V2H12V4ZM2 16H6V14H2V16Z" fill="#434655"/>
                            </svg>
                        </span>
                        Dashboard
                    </a>
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V4C0 3.45 0.195833 2.97917 0.5875 2.5875C0.979167 2.19583 1.45 2 2 2H3V0H5V2H13V0H15V2H16C16.55 2 17.0208 2.19583 17.4125 2.5875C17.8042 2.97917 18 3.45 18 4V18C18 18.55 17.8042 19.0208 17.4125 19.4125C17.0208 19.8042 16.55 20 16 20H2ZM2 18H16V8H2V18ZM2 6H16V4H2V6ZM2 6V4V6ZM4 12V10H14V12H4ZM4 16V14H11V16H4Z" fill="#434655"/>
                            </svg>
                        </span>
                        Reservations
                    </a>
                    <a href="#" class="sidebar-link is-active">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V6C0 5.45 0.195833 4.97917 0.5875 4.5875C0.979167 4.19583 1.45 4 2 4H6V2C6 1.45 6.19583 0.979167 6.5875 0.5875C6.97917 0.195833 7.45 0 8 0H12C12.55 0 13.0208 0.195833 13.4125 0.5875C13.8042 0.979167 14 1.45 14 2V4H18C18.55 4 19.0208 4.19583 19.4125 4.5875C19.8042 4.97917 20 5.45 20 6V18C20 18.55 19.8042 19.0208 19.4125 19.4125C19.0208 19.8042 18.55 20 18 20H2ZM8 4H12V2H8V4ZM9 13V16H11V13H14V11H11V8H9V11H6V13H9Z" fill="#006F66"/>
                            </svg>
                        </span>
                        Doctors
                    </a>
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V4C0 3.45 0.195833 2.97917 0.5875 2.5875C0.979167 2.19583 1.45 2 2 2H3V0H5V2H13V0H15V2H16C16.55 2 17.0208 2.19583 17.4125 2.5875C17.8042 2.97917 18 3.45 18 4V18C18 18.55 17.8042 19.0208 17.4125 19.4125C17.0208 19.8042 16.55 20 16 20H2ZM2 18H16V8H2V18ZM2 6H16V4H2V6ZM2 6V4V6Z" fill="#434655"/>
                            </svg>
                        </span>
                        Schedule
                    </a>
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 13H17V12.45C17 11.7 16.6333 11.1042 15.9 10.6625C15.1667 10.2208 14.2 10 13 10C11.8 10 10.8333 10.2208 10.1 10.6625C9.36667 11.1042 9 11.7 9 12.45V13ZM13 9C13.55 9 14.0208 8.80417 14.4125 8.4125C14.8042 8.02083 15 7.55 15 7C15 6.45 14.8042 5.97917 14.4125 5.5875C14.0208 5.19583 13.55 5 13 5C12.45 5 11.9792 5.19583 11.5875 5.5875C11.1958 5.97917 11 6.45 11 7C11 7.55 11.1958 8.02083 11.5875 8.4125C11.9792 8.80417 12.45 9 13 9ZM2 16C1.45 16 0.979167 15.8042 0.5875 15.4125C0.195833 15.0208 0 14.55 0 14V2C0 1.45 0.195833 0.979167 0.5875 0.5875C0.979167 0.195833 1.45 0 2 0H8L10 2H18C18.55 2 19.0208 2.19583 19.4125 2.5875C19.8042 2.97917 20 3.45 20 4V14C20 14.55 19.8042 15.0208 19.4125 15.4125C19.0208 15.8042 18.55 16 18 16H2ZM2 14H18V4H9.175L7.175 2H2V14ZM2 14V2V4V14Z" fill="#434655"/>
                            </svg>
                        </span>
                        Records
                    </a>
                </nav>

                <div class="sidebar-footer">
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.95 16C10.3 16 10.5958 15.8792 10.8375 15.6375C11.0792 15.3958 11.2 15.1 11.2 14.75C11.2 14.4 11.0792 14.1042 10.8375 13.8625C10.5958 13.6208 10.3 13.5 9.95 13.5C9.6 13.5 9.30417 13.6208 9.0625 13.8625C8.82083 14.1042 8.7 14.4 8.7 14.75C8.7 15.1 8.82083 15.3958 9.0625 15.6375C9.30417 15.8792 9.6 16 9.95 16ZM9.05 12.15H10.9C10.9 11.6 10.9625 11.1667 11.0875 10.85C11.2125 10.5333 11.5667 10.1 12.15 9.55C12.5833 9.11667 12.925 8.70417 13.175 8.3125C13.425 7.92083 13.55 7.45 13.55 6.9C13.55 5.96667 13.2083 5.25 12.525 4.75C11.8417 4.25 11.0333 4 10.1 4C9.15 4 8.37917 4.25 7.7875 4.75C7.19583 5.25 6.78333 5.85 6.55 6.55L8.2 7.2C8.28333 6.9 8.47083 6.575 8.7625 6.225C9.05417 5.875 9.5 5.7 10.1 5.7C10.6333 5.7 11.0333 5.84583 11.3 6.1375C11.5667 6.42917 11.7 6.75 11.7 7.1C11.7 7.43333 11.6 7.74583 11.4 8.0375C11.2 8.32917 10.95 8.6 10.65 8.85C9.91667 9.5 9.46667 9.99167 9.3 10.325C9.13333 10.6583 9.05 11.2667 9.05 12.15ZM10 20C8.61667 20 7.31667 19.7375 6.1 19.2125C4.88333 18.6875 3.825 17.975 2.925 17.075C2.025 16.175 1.3125 15.1167 0.7875 13.9C0.2625 12.6833 0 11.3833 0 10C0 8.61667 0.2625 7.31667 0.7875 6.1C1.3125 4.88333 2.025 3.825 2.925 2.925C3.825 2.025 4.88333 1.3125 6.1 0.7875C7.31667 0.2625 8.61667 0 10 0C11.3833 0 12.6833 0.2625 13.9 0.7875C15.1167 1.3125 16.175 2.025 17.075 2.925C17.975 3.825 18.6875 4.88333 19.2125 6.1C19.7375 7.31667 20 8.61667 20 10C20 11.3833 19.7375 12.6833 19.2125 13.9C18.6875 15.1167 17.975 16.175 17.075 17.075C16.175 17.975 15.1167 18.6875 13.9 19.2125C12.6833 19.7375 11.3833 20 10 20ZM10 18C12.2333 18 14.125 17.225 15.675 15.675C17.225 14.125 18 12.2333 18 10C18 7.76667 17.225 5.875 15.675 4.325C14.125 2.775 12.2333 2 10 2C7.76667 2 5.875 2.775 4.325 4.325C2.775 5.875 2 7.76667 2 10C2 12.2333 2.775 14.125 4.325 15.675C5.875 17.225 7.76667 18 10 18Z" fill="#434655"/>
                            </svg>
                        </span>
                        Help Center
                    </a>
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 18C1.45 18 0.979167 17.8042 0.5875 17.4125C0.195833 17.0208 0 16.55 0 16V2C0 1.45 0.195833 0.979167 0.5875 0.5875C0.979167 0.195833 1.45 0 2 0H9V2H2V16H9V18H2ZM13 14L11.625 12.55L14.175 10H6V8H14.175L11.625 5.45L13 4L18 9L13 14Z" fill="#434655"/>
                            </svg>
                        </span>
                        Logout
                    </a>
                </div>
            </aside>

            <main class="booking-main">
                <header class="booking-topbar">
                    <div class="topbar-logo">ClinicReserve</div>
                    <label class="topbar-search">
                        <span class="search-dot"></span>
                        <input type="search" placeholder="Search patients or providers..." aria-label="Search patients or providers">
                        <kbd>Ctrl K</kbd>
                    </label>
                    <div class="topbar-actions" aria-label="Account actions">
                        <button class="topbar-icon-button topbar-figma-icon" type="button" aria-label="Notifications">
                            <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 17V15H2V8C2 6.61667 2.41667 5.3875 3.25 4.3125C4.08333 3.2375 5.16667 2.53333 6.5 2.2V1.5C6.5 1.08333 6.64583 0.729167 6.9375 0.4375C7.22917 0.145833 7.58333 0 8 0C8.41667 0 8.77083 0.145833 9.0625 0.4375C9.35417 0.729167 9.5 1.08333 9.5 1.5V2.2C10.8333 2.53333 11.9167 3.2375 12.75 4.3125C13.5833 5.3875 14 6.61667 14 8V15H16V17H0ZM8 20C7.45 20 6.97917 19.8042 6.5875 19.4125C6.19583 19.0208 6 18.55 6 18H10C10 18.55 9.80417 19.0208 9.4125 19.4125C9.02083 19.8042 8.55 20 8 20ZM4 15H12V8C12 6.9 11.6083 5.95833 10.825 5.175C10.0417 4.39167 9.1 4 8 4C6.9 4 5.95833 4.39167 5.175 5.175C4.39167 5.95833 4 6.9 4 8V15Z" fill="#434655"/>
                            </svg>
                        </button>
                        <button class="topbar-icon-button topbar-figma-icon" type="button" aria-label="Settings">
                            <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.3 20L6.9 16.8C6.68333 16.7167 6.47917 16.6167 6.2875 16.5C6.09583 16.3833 5.90833 16.2583 5.725 16.125L2.75 17.375L0 12.625L2.575 10.675C2.55833 10.5583 2.55 10.4458 2.55 10.3375C2.55 10.2292 2.55 10.1167 2.55 10C2.55 9.88333 2.55 9.77083 2.55 9.6625C2.55 9.55417 2.55833 9.44167 2.575 9.325L0 7.375L2.75 2.625L5.725 3.875C5.90833 3.74167 6.1 3.61667 6.3 3.5C6.5 3.38333 6.7 3.28333 6.9 3.2L7.3 0H12.8L13.2 3.2C13.4167 3.28333 13.6208 3.38333 13.8125 3.5C14.0042 3.61667 14.1917 3.74167 14.375 3.875L17.35 2.625L20.1 7.375L17.525 9.325C17.5417 9.44167 17.55 9.55417 17.55 9.6625C17.55 9.77083 17.55 9.88333 17.55 10C17.55 10.1167 17.55 10.2292 17.55 10.3375C17.55 10.4458 17.5333 10.5583 17.5 10.675L20.075 12.625L17.325 17.375L14.375 16.125C14.1917 16.2583 14 16.3833 13.8 16.5C13.6 16.6167 13.4 16.7167 13.2 16.8L12.8 20H7.3ZM9.05 18H11.025L11.375 15.35C11.8917 15.2167 12.3708 15.0208 12.8125 14.7625C13.2542 14.5042 13.6583 14.1917 14.025 13.825L16.5 14.85L17.475 13.15L15.325 11.525C15.4083 11.2917 15.4667 11.0458 15.5 10.7875C15.5333 10.5292 15.55 10.2667 15.55 10C15.55 9.73333 15.5333 9.47083 15.5 9.2125C15.4667 8.95417 15.4083 8.70833 15.325 8.475L17.475 6.85L16.5 5.15L14.025 6.2C13.6583 5.81667 13.2542 5.49583 12.8125 5.2375C12.3708 4.97917 11.8917 4.78333 11.375 4.65L11.05 2H9.075L8.725 4.65C8.20833 4.78333 7.72917 4.97917 7.2875 5.2375C6.84583 5.49583 6.44167 5.80833 6.075 6.175L3.6 5.15L2.625 6.85L4.775 8.45C4.69167 8.7 4.63333 8.95 4.6 9.2C4.56667 9.45 4.55 9.71667 4.55 10C4.55 10.2667 4.56667 10.525 4.6 10.775C4.63333 11.025 4.69167 11.275 4.775 11.525L2.625 13.15L3.6 14.85L6.075 13.8C6.44167 14.1833 6.84583 14.5042 7.2875 14.7625C7.72917 15.0208 8.20833 15.2167 8.725 15.35L9.05 18ZM10.1 13.5C11.0667 13.5 11.8917 13.1583 12.575 12.475C13.2583 11.7917 13.6 10.9667 13.6 10C13.6 9.03333 13.2583 8.20833 12.575 7.525C11.8917 6.84167 11.0667 6.5 10.1 6.5C9.11667 6.5 8.2875 6.84167 7.6125 7.525C6.9375 8.20833 6.6 9.03333 6.6 10C6.6 10.9667 6.9375 11.7917 7.6125 12.475C8.2875 13.1583 9.11667 13.5 10.1 13.5Z" fill="#434655"/>
                            </svg>
                        </button>
                        <span class="topbar-divider"></span>
                        <a href="#">Logout</a>
                        <span class="avatar-placeholder">AJ</span>
                    </div>
                </header>

                <section class="booking-content">
                    <div class="page-heading">
                        <p>Doctors / <span>Book Appointment</span></p>
                        <h1>Schedule Consultation</h1>
                    </div>

                    <div class="booking-rules" role="alert">
                        <div class="rules-icon">i</div>
                        <div>
                            <strong>Important Booking Rules</strong>
                            <p>Please note that cancellations made less than 24 hours prior to the appointment will incur a $50 late-cancellation fee. Ensure you arrive 15 minutes early for intake processing.</p>
                        </div>
                    </div>

                    <div id="booking-feedback" class="booking-feedback is-hidden" aria-live="polite"></div>

                    <div class="booking-grid">
                        <div class="booking-left-column">
                            <section class="booking-card doctor-card" aria-labelledby="doctor-card-title">
                                <div id="doctor-image" class="doctor-image-placeholder">SJ</div>
                                <div class="doctor-details">
                                    <div class="doctor-title-row">
                                        <h2 id="doctor-card-title">Loading doctor...</h2>
                                        <span id="doctor-specialty" class="specialty-badge">Specialty</span>
                                    </div>
                                    <p id="doctor-bio" class="doctor-bio">Please wait while we load the doctor profile.</p>
                                    <div class="doctor-meta">
                                        <span class="doctor-meta-item">
                                            <span class="doctor-meta-icon" aria-hidden="true">
                                                <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5.70833 12.3542L8.33333 10.7708L10.9583 12.375L10.2708 9.375L12.5833 7.375L9.54167 7.10417L8.33333 4.27083L7.125 7.08333L4.08333 7.35417L6.39583 9.375L5.70833 12.3542ZM3.1875 15.8333L4.54167 9.97917L0 6.04167L6 5.52083L8.33333 0L10.6667 5.52083L16.6667 6.04167L12.125 9.97917L13.4792 15.8333L8.33333 12.7292L3.1875 15.8333Z" fill="#434655"/>
                                                </svg>
                                            </span>
                                            <span id="doctor-rating">4.9 (120 reviews)</span>
                                        </span>
                                        <span class="doctor-meta-item">
                                            <span class="doctor-meta-icon" aria-hidden="true">
                                                <svg width="14" height="17" viewBox="0 0 14 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M6.66667 8.33333C7.125 8.33333 7.51736 8.17014 7.84375 7.84375C8.17014 7.51736 8.33333 7.125 8.33333 6.66667C8.33333 6.20833 8.17014 5.81597 7.84375 5.48958C7.51736 5.16319 7.125 5 6.66667 5C6.20833 5 5.81597 5.16319 5.48958 5.48958C5.16319 5.81597 5 6.20833 5 6.66667C5 7.125 5.16319 7.51736 5.48958 7.84375C5.81597 8.17014 6.20833 8.33333 6.66667 8.33333ZM6.66667 14.4583C8.36111 12.9028 9.61806 11.4896 10.4375 10.2188C11.2569 8.94792 11.6667 7.81944 11.6667 6.83333C11.6667 5.31944 11.184 4.07986 10.2188 3.11458C9.25347 2.14931 8.06944 1.66667 6.66667 1.66667C5.26389 1.66667 4.07986 2.14931 3.11458 3.11458C2.14931 4.07986 1.66667 5.31944 1.66667 6.83333C1.66667 7.81944 2.07639 8.94792 2.89583 10.2188C3.71528 11.4896 4.97222 12.9028 6.66667 14.4583ZM6.66667 16.6667C4.43056 14.7639 2.76042 12.9965 1.65625 11.3646C0.552083 9.73264 0 8.22222 0 6.83333C0 4.75 0.670139 3.09028 2.01042 1.85417C3.35069 0.618055 4.90278 0 6.66667 0C8.43056 0 9.98264 0.618055 11.3229 1.85417C12.6632 3.09028 13.3333 4.75 13.3333 6.83333C13.3333 8.22222 12.7812 9.73264 11.6771 11.3646C10.5729 12.9965 8.90278 14.7639 6.66667 16.6667Z" fill="#434655"/>
                                                </svg>
                                            </span>
                                            <span id="doctor-location">Building A, Suite 302</span>
                                        </span>
                                    </div>
                                </div>
                            </section>

                            <section class="booking-card date-card" aria-labelledby="date-card-title">
                                <div class="card-heading-row">
                                    <h2 id="date-card-title">Select Date</h2>
                                    <div class="date-controls">
                                        <button id="date-prev" type="button" aria-label="Previous dates">&lt;</button>
                                        <button id="date-next" type="button" aria-label="Next dates">&gt;</button>
                                    </div>
                                </div>
                                <div id="date-options" class="date-options"></div>
                            </section>

                            <section class="booking-card slots-card" aria-labelledby="slots-card-title">
                                <h2 id="slots-card-title">Available Time Slots</h2>
                                <div id="slot-groups" class="slot-groups">
                                    <p class="loading-text">Select a date to view available slots.</p>
                                </div>
                            </section>

                            <section class="booking-card patient-card" aria-labelledby="patient-card-title">
                                <h2 id="patient-card-title">Patient Details</h2>
                                <div class="patient-form-grid">
                                    <label>
                                        <span>Name</span>
                                        <input id="patient-name" type="text" autocomplete="name" placeholder="Enter patient name">
                                    </label>
                                    <label>
                                        <span>Email</span>
                                        <input id="patient-email" type="email" autocomplete="email" placeholder="name@example.com">
                                    </label>
                                    <label>
                                        <span>Phone</span>
                                        <input id="patient-phone" type="tel" autocomplete="tel" placeholder="01012345678">
                                    </label>
                                    <label class="notes-field">
                                        <span>Notes</span>
                                        <textarea id="patient-notes" rows="3" placeholder="Optional notes for the clinic"></textarea>
                                    </label>
                                </div>
                            </section>
                        </div>

                        <aside class="booking-summary" aria-labelledby="summary-title">
                            <h2 id="summary-title">Booking Summary</h2>

                            <div class="summary-list">
                                <div class="summary-item">
                                    <span class="summary-icon summary-figma-icon" aria-hidden="true">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 16H7V14H9V12H7V10H5V12H3V14H5V16ZM11 12.5H17V11H11V12.5ZM11 15.5H15V14H11V15.5ZM2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V7C0 6.45 0.195833 5.97917 0.5875 5.5875C0.979167 5.19583 1.45 5 2 5H7V2C7 1.45 7.19583 0.979167 7.5875 0.5875C7.97917 0.195833 8.45 0 9 0H11C11.55 0 12.0208 0.195833 12.4125 0.5875C12.8042 0.979167 13 1.45 13 2V5H18C18.55 5 19.0208 5.19583 19.4125 5.5875C19.8042 5.97917 20 6.45 20 7V18C20 18.55 19.8042 19.0208 19.4125 19.4125C19.0208 19.8042 18.55 20 18 20H2ZM2 18H18V7H13C13 7.55 12.8042 8.02083 12.4125 8.4125C12.0208 8.80417 11.55 9 11 9H9C8.45 9 7.97917 8.80417 7.5875 8.4125C7.19583 8.02083 7 7.55 7 7H2V18ZM9 7H11V2H9V7Z" fill="#004AC6"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <small>Consultation with</small>
                                        <strong id="summary-doctor">Dr. Sarah Jenkins</strong>
                                        <p id="summary-specialty">Cardiology Specialist</p>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-icon summary-figma-icon" aria-hidden="true">
                                        <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11.5 16C10.8 16 10.2083 15.7583 9.725 15.275C9.24167 14.7917 9 14.2 9 13.5C9 12.8 9.24167 12.2083 9.725 11.725C10.2083 11.2417 10.8 11 11.5 11C12.2 11 12.7917 11.2417 13.275 11.725C13.7583 12.2083 14 12.8 14 13.5C14 14.2 13.7583 14.7917 13.275 15.275C12.7917 15.7583 12.2 16 11.5 16ZM2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V4C0 3.45 0.195833 2.97917 0.5875 2.5875C0.979167 2.19583 1.45 2 2 2H3V0H5V2H13V0H15V2H16C16.55 2 17.0208 2.19583 17.4125 2.5875C17.8042 2.97917 18 3.45 18 4V18C18 18.55 17.8042 19.0208 17.4125 19.4125C17.0208 19.8042 16.55 20 16 20H2ZM2 18H16V8H2V18ZM2 6H16V4H2V6ZM2 6V4V6Z" fill="#004AC6"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <small>Date &amp; Time</small>
                                        <strong id="summary-date">Select date</strong>
                                        <p id="summary-time">Select time slot</p>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-icon summary-figma-icon" aria-hidden="true">
                                        <svg width="14" height="17" viewBox="0 0 14 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6.66667 8.33333C7.125 8.33333 7.51736 8.17014 7.84375 7.84375C8.17014 7.51736 8.33333 7.125 8.33333 6.66667C8.33333 6.20833 8.17014 5.81597 7.84375 5.48958C7.51736 5.16319 7.125 5 6.66667 5C6.20833 5 5.81597 5.16319 5.48958 5.48958C5.16319 5.81597 5 6.20833 5 6.66667C5 7.125 5.16319 7.51736 5.48958 7.84375C5.81597 8.17014 6.20833 8.33333 6.66667 8.33333ZM6.66667 14.4583C8.36111 12.9028 9.61806 11.4896 10.4375 10.2188C11.2569 8.94792 11.6667 7.81944 11.6667 6.83333C11.6667 5.31944 11.184 4.07986 10.2188 3.11458C9.25347 2.14931 8.06944 1.66667 6.66667 1.66667C5.26389 1.66667 4.07986 2.14931 3.11458 3.11458C2.14931 4.07986 1.66667 5.31944 1.66667 6.83333C1.66667 7.81944 2.07639 8.94792 2.89583 10.2188C3.71528 11.4896 4.97222 12.9028 6.66667 14.4583ZM6.66667 16.6667C4.43056 14.7639 2.76042 12.9965 1.65625 11.3646C0.552083 9.73264 0 8.22222 0 6.83333C0 4.75 0.670139 3.09028 2.01042 1.85417C3.35069 0.618055 4.90278 0 6.66667 0C8.43056 0 9.98264 0.618055 11.3229 1.85417C12.6632 3.09028 13.3333 4.75 13.3333 6.83333C13.3333 8.22222 12.7812 9.73264 11.6771 11.3646C10.5729 12.9965 8.90278 14.7639 6.66667 16.6667Z" fill="#004AC6"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <small>Location</small>
                                        <strong id="summary-location">Main City Hospital</strong>
                                        <p id="summary-room">Building A, Suite 302</p>
                                    </div>
                                </div>
                            </div>

                            <div class="summary-costs">
                                <div>
                                    <span>Consultation Fee</span>
                                    <strong id="summary-consultation-fee">$150.00</strong>
                                </div>
                                <div>
                                    <span>Processing</span>
                                    <strong id="summary-processing-fee">$5.00</strong>
                                </div>
                            </div>

                            <div class="summary-total">
                                <span>Total Estimated</span>
                                <strong id="summary-total">$155.00</strong>
                            </div>

                            <button id="confirm-appointment" class="confirm-button" type="button" disabled>
                                <span class="confirm-button-icon" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.6 14.6L15.65 7.55L14.25 6.15L8.6 11.8L5.75 8.95L4.35 10.35L8.6 14.6ZM10 20C8.61667 20 7.31667 19.7375 6.1 19.2125C4.88333 18.6875 3.825 17.975 2.925 17.075C2.025 16.175 1.3125 15.1167 0.7875 13.9C0.2625 12.6833 0 11.3833 0 10C0 8.61667 0.2625 7.31667 0.7875 6.1C1.3125 4.88333 2.025 3.825 2.925 2.925C3.825 2.025 4.88333 1.3125 6.1 0.7875C7.31667 0.2625 8.61667 0 10 0C11.3833 0 12.6833 0.2625 13.9 0.7875C15.1167 1.3125 16.175 2.025 17.075 2.925C17.975 3.825 18.6875 4.88333 19.2125 6.1C19.7375 7.31667 20 8.61667 20 10C20 11.3833 19.7375 12.6833 19.2125 13.9C18.6875 15.1167 17.975 16.175 17.075 17.075C16.175 17.975 15.1167 18.6875 13.9 19.2125C12.6833 19.7375 11.3833 20 10 20ZM10 18C12.2333 18 14.125 17.225 15.675 15.675C17.225 14.125 18 12.2333 18 10C18 7.76667 17.225 5.875 15.675 4.325C14.125 2.775 12.2333 2 10 2C7.76667 2 5.875 2.775 4.325 4.325C2.775 5.875 2 7.76667 2 10C2 12.2333 2.775 14.125 4.325 15.675C5.875 17.225 7.76667 18 10 18Z" fill="white"/>
                                    </svg>
                                </span>
                                Confirm Appointment
                            </button>

                            <p class="secure-note">
                                <span class="secure-note-icon" aria-hidden="true">
                                    <svg width="11" height="14" viewBox="0 0 11 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.33333 14C0.966667 14 0.652778 13.8694 0.391667 13.6083C0.130556 13.3472 0 13.0333 0 12.6667V6C0 5.63333 0.130556 5.31944 0.391667 5.05833C0.652778 4.79722 0.966667 4.66667 1.33333 4.66667H2V3.33333C2 2.41111 2.325 1.625 2.975 0.975C3.625 0.325 4.41111 0 5.33333 0C6.25556 0 7.04167 0.325 7.69167 0.975C8.34167 1.625 8.66667 2.41111 8.66667 3.33333V4.66667H9.33333C9.7 4.66667 10.0139 4.79722 10.275 5.05833C10.5361 5.31944 10.6667 5.63333 10.6667 6V12.6667C10.6667 13.0333 10.5361 13.3472 10.275 13.6083C10.0139 13.8694 9.7 14 9.33333 14H1.33333ZM1.33333 12.6667H9.33333V6H1.33333V12.6667ZM5.33333 10.6667C5.7 10.6667 6.01389 10.5361 6.275 10.275C6.53611 10.0139 6.66667 9.7 6.66667 9.33333C6.66667 8.96667 6.53611 8.65278 6.275 8.39167C6.01389 8.13056 5.7 8 5.33333 8C4.96667 8 4.65278 8.13056 4.39167 8.39167C4.13056 8.65278 4 8.96667 4 9.33333C4 9.7 4.13056 10.0139 4.39167 10.275C4.65278 10.5361 4.96667 10.6667 5.33333 10.6667ZM3.33333 4.66667H7.33333V3.33333C7.33333 2.77778 7.13889 2.30556 6.75 1.91667C6.36111 1.52778 5.88889 1.33333 5.33333 1.33333C4.77778 1.33333 4.30556 1.52778 3.91667 1.91667C3.52778 2.30556 3.33333 2.77778 3.33333 3.33333V4.66667ZM1.33333 12.6667V6V12.6667Z" fill="#434655"/>
                                    </svg>
                                </span>
                                Secure booking process
                            </p>
                        </aside>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
