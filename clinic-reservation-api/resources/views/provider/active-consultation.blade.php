<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Active Consultation | ClinicReserve</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="consultation-page" data-page="provider-consultation" data-patient-id="{{ $patientId }}" data-doctor-id="{{ $doctorId }}">
        <div class="consultation-shell">
             <aside class="booking-sidebar" aria-label="Provider navigation">
                <div class="sidebar-brand">
                    <strong>Clinic Management</strong>
                    <span>Provider Portal</span>
                </div>

                <nav class="sidebar-nav">
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 6V0H18V6H10ZM0 10V0H8V10H0ZM10 18V8H18V18H10ZM0 18V12H8V18H0ZM2 8H6V2H2V8ZM12 16H16V10H12V16ZM12 4H16V2H12V4ZM2 16H6V14H2V16Z" fill="#434655"/>
                            </svg>
                        </span>
                        Dashboard
                    </a>
                    <a href="{{ route('provider.active-consultation', [$patientId, $doctorId]) }}" class="sidebar-link is-active">
                        <span class="sidebar-icon sidebar-figma-icon" aria-hidden="true">
                            <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 20C1.45 20 0.979167 19.8042 0.5875 19.4125C0.195833 19.0208 0 18.55 0 18V4C0 3.45 0.195833 2.97917 0.5875 2.5875C0.979167 2.19583 1.45 2 2 2H3V0H5V2H13V0H15V2H16C16.55 2 17.0208 2.19583 17.4125 2.5875C17.8042 2.97917 18 3.45 18 4V18C18 18.55 17.8042 19.0208 17.4125 19.4125C17.0208 19.8042 16.55 20 16 20H2ZM2 18H16V8H2V18ZM2 6H16V4H2V6ZM2 6V4V6ZM4 12V10H14V12H4ZM4 16V14H11V16H4Z" fill="#434655"/>
                            </svg>
                        </span>
                        Reservations
                    </a>
                    <a href="{{ route('patient.book-appointment') }}" class="sidebar-link" data-absolute-href="{{ route('patient.book-appointment') }}">
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


            <main class="consultation-main">
                <header class="consultation-topbar">
                    <strong>ClinicReserve</strong>
                    <label class="consultation-search">
                        <span></span>
                        <input type="search" placeholder="Search patient records..." aria-label="Search patient records">
                    </label>
                    <div class="consultation-actions">
                        <button type="button" aria-label="Notifications">♧</button>
                        <button type="button" aria-label="Settings">⚙</button>
                        <a href="#">Logout</a>
                    </div>
                </header>

                <section class="consultation-content">
                    <div class="consultation-heading">
                        <p id="consultation-meta">Today, 10:30 AM — Dr. Sarah Jenkins</p>
                        <h1>Active Consultation</h1>
                        <span id="consultation-status">Session In Progress</span>
                    </div>

                    <div id="consultation-feedback" class="consultation-feedback is-hidden" aria-live="polite"></div>

                    <div class="consultation-grid">
                        <section class="patient-panel" aria-labelledby="patient-name">
                            <div class="patient-identity">
                                <div id="patient-avatar" class="patient-avatar">EV</div>
                                <div>
                                    <h2 id="patient-name">Eleanor Vance</h2>
                                    <p id="patient-demographics">DOB: 12/04/1978 (45y) • F</p>
                                    <small id="patient-record">ID: PT-88492-X</small>
                                </div>
                            </div>

                            <div class="vitals-row">
                                <div><small>Blood Type</small><strong id="vital-blood">A+</strong></div>
                                <div><small>Weight</small><strong id="vital-weight">68 kg</strong></div>
                                <div><small>BP (Last)</small><strong id="vital-bp">118/76</strong></div>
                            </div>

                            <div id="allergy-list" class="allergy-list"></div>

                            <div class="history-heading">
                                <strong>Medical History</strong>
                                <button id="new-consultation-record" type="button" aria-label="Add consultation record">+</button>
                            </div>
                            <div id="history-list" class="history-list"></div>
                            <p class="history-end">End of recent records</p>
                        </section>

                        <form id="consultation-form" class="records-panel">
                            <section class="clinical-card" aria-labelledby="clinical-title">
                                <h2 id="clinical-title">⌑ Clinical Notes</h2>
                                <label>
                                    <span>Chief Complaint / Subjective</span>
                                    <textarea id="chief-complaint" rows="4" placeholder="Describe the patient's symptoms and perspective..."></textarea>
                                </label>
                                <label>
                                    <span>Objective Observations</span>
                                    <textarea id="objective-observations" rows="4" placeholder="Enter measurable data, physical exam findings..."></textarea>
                                </label>
                                <label>
                                    <span>Assessment & Plan</span>
                                    <textarea id="assessment-plan" rows="4" placeholder="Diagnosis and proposed treatment steps..."></textarea>
                                </label>
                            </section>

                            <section class="prescription-card" aria-labelledby="prescription-title">
                                <div class="prescription-heading">
                                    <h2 id="prescription-title">⚕ Prescription Entry</h2>
                                    <button type="button" id="add-medication">⊕ Add Medication</button>
                                </div>
                                <div id="medication-list" class="medication-list"></div>
                                <label>
                                    <span>Pharmacy Instructions</span>
                                    <input id="pharmacy-instructions" type="text" placeholder="Optional notes for the pharmacist...">
                                </label>
                            </section>

                            <div class="consultation-footer">
                                <button id="save-records" type="submit">▣ Save Records</button>
                                <button id="complete-session" type="button">✓ Mark as Complete</button>
                            </div>
                        </form>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
