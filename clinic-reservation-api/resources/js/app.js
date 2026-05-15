document.addEventListener('DOMContentLoaded', () => {
    if (document.body.dataset.page !== 'patient-booking') {
        return;
    }

    const state = {
        doctor: null,
        selectedDate: null,
        selectedSlot: null,
        dateWindowStart: startOfToday(),
        visibleDates: [],
        slotGroups: [],
        submitting: false,
        bookingComplete: false,
        confirmedReservation: null,
    };

    const els = {
        doctorImage: document.getElementById('doctor-image'),
        doctorName: document.getElementById('doctor-card-title'),
        doctorSpecialty: document.getElementById('doctor-specialty'),
        doctorBio: document.getElementById('doctor-bio'),
        doctorRating: document.getElementById('doctor-rating'),
        doctorLocation: document.getElementById('doctor-location'),
        dateOptions: document.getElementById('date-options'),
        datePrev: document.getElementById('date-prev'),
        dateNext: document.getElementById('date-next'),
        slotGroups: document.getElementById('slot-groups'),
        feedback: document.getElementById('booking-feedback'),
        summaryDoctor: document.getElementById('summary-doctor'),
        summarySpecialty: document.getElementById('summary-specialty'),
        summaryDate: document.getElementById('summary-date'),
        summaryTime: document.getElementById('summary-time'),
        summaryLocation: document.getElementById('summary-location'),
        summaryRoom: document.getElementById('summary-room'),
        summaryConsultationFee: document.getElementById('summary-consultation-fee'),
        summaryProcessingFee: document.getElementById('summary-processing-fee'),
        summaryTotal: document.getElementById('summary-total'),
        patientName: document.getElementById('patient-name'),
        patientEmail: document.getElementById('patient-email'),
        patientPhone: document.getElementById('patient-phone'),
        patientNotes: document.getElementById('patient-notes'),
        confirmButton: document.getElementById('confirm-appointment'),
    };

    els.datePrev.addEventListener('click', () => shiftDates(-7));
    els.dateNext.addEventListener('click', () => shiftDates(7));
    els.confirmButton.addEventListener('click', () => {
        if (state.bookingComplete) {
            startAnotherBooking();
            return;
        }

        submitReservation();
    });

    loadInitialDoctor();

    async function loadInitialDoctor() {
        try {
            const routeDoctorId = document.body.dataset.doctorId;

            if (routeDoctorId) {
                await loadDoctor(routeDoctorId);
            } else {
                const response = await apiRequest('/api/doctors');
                const doctors = response.data?.doctors ?? [];

                if (! doctors.length) {
                    showFeedback('warning', 'No doctors are available yet. Run the database seeder to add sample doctors.');
                    return;
                }

                state.doctor = findDefaultDoctor(doctors);
            }

            renderDoctor();
            renderDates();
            await loadSlots();
        } catch (error) {
            showFeedback('warning', error.message || 'Unable to load booking information.');
        }
    }

    async function loadDoctor(doctorId) {
        const response = await apiRequest(`/api/doctors/${doctorId}`);
        state.doctor = response.data?.doctor;

        if (! state.doctor) {
            throw new Error('Doctor profile could not be loaded.');
        }
    }

    async function loadSlots() {
        if (! state.doctor || ! state.selectedDate) {
            return;
        }

        els.slotGroups.innerHTML = '<p class="loading-text">Loading available slots...</p>';

        try {
            const response = await apiRequest(`/api/doctors/${state.doctor.id}/available-slots?date=${state.selectedDate}`);
            renderSlots(response.data?.slot_groups ?? []);
        } catch (error) {
            state.selectedSlot = null;
            els.slotGroups.innerHTML = '<p class="loading-text">Unable to load slots for this date.</p>';
            showFeedback('warning', error.message || 'Please select another date.');
        }

        updateSummary();
    }

    function renderDoctor() {
        const doctor = state.doctor;
        const locationParts = splitLocation(doctor.location);

        els.doctorName.textContent = doctor.name || 'Doctor';
        els.doctorSpecialty.textContent = (doctor.specialty || 'General Care').toUpperCase();
        els.doctorBio.textContent = doctor.bio || 'Experienced provider available for patient consultations.';
        els.doctorRating.textContent = `${doctor.rating || '4.9'} (120 reviews)`;
        els.doctorLocation.textContent = doctor.location || 'Main City Hospital';

        if (doctor.image) {
            els.doctorImage.classList.remove('is-illustration');
            els.doctorImage.innerHTML = '';
            const image = document.createElement('img');
            image.src = doctor.image;
            image.alt = doctor.name || 'Doctor profile';
            els.doctorImage.appendChild(image);
        } else {
            els.doctorImage.classList.add('is-illustration');
            els.doctorImage.innerHTML = doctorIllustration();
        }

        els.summaryDoctor.textContent = doctor.name || 'Doctor';
        els.summarySpecialty.textContent = `${doctor.specialty || 'General Care'} Specialist`;
        els.summaryLocation.textContent = locationParts.primary;
        els.summaryRoom.textContent = locationParts.secondary;
        updateFees();
    }

    function renderDates() {
        state.visibleDates = Array.from({ length: 7 }, (_, index) => {
            const date = new Date(state.dateWindowStart);
            date.setDate(date.getDate() + index);
            return date;
        });

        if (! state.selectedDate) {
            const defaultDate = state.visibleDates[Math.min(3, state.visibleDates.length - 1)];
            state.selectedDate = toIsoDate(defaultDate);
        }

        els.dateOptions.innerHTML = '';

        state.visibleDates.forEach((date) => {
            const isoDate = toIsoDate(date);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `date-option${isoDate === state.selectedDate ? ' is-selected' : ''}`;
            button.dataset.date = isoDate;

            const weekday = document.createElement('span');
            weekday.textContent = new Intl.DateTimeFormat('en', { weekday: 'short' }).format(date);

            const day = document.createElement('strong');
            day.textContent = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(date);

            button.append(weekday, day);
            button.addEventListener('click', async () => {
                resetCompletedBooking();
                state.selectedDate = isoDate;
                state.selectedSlot = null;
                renderDates();
                await loadSlots();
            });

            els.dateOptions.appendChild(button);
        });

        els.datePrev.disabled = toIsoDate(state.dateWindowStart) <= toIsoDate(startOfToday());
        updateSummary();
    }

    function renderSlots(groups) {
        els.slotGroups.innerHTML = '';
        state.slotGroups = groups;

        const availableSlots = groups.flatMap((group) => group.slots)
            .filter((slot) => slot.available)
            .map((slot) => slot.time);

        if (! state.bookingComplete && state.selectedSlot && ! availableSlots.includes(state.selectedSlot)) {
            state.selectedSlot = null;
        }

        if (! state.bookingComplete && ! state.selectedSlot && availableSlots.length) {
            state.selectedSlot = availableSlots[0];
        }

        groups.forEach((group) => {
            const groupEl = document.createElement('div');
            groupEl.className = 'slot-group';

            const title = document.createElement('h3');
            title.textContent = group.period;
            groupEl.appendChild(title);

            const list = document.createElement('div');
            list.className = 'slot-list';

            group.slots.forEach((slot) => {
                const isConfirmedSlot = isConfirmedReservationSlot(slot.time);

                if (! state.bookingComplete && slot.time === state.selectedSlot && ! slot.available) {
                    state.selectedSlot = null;
                }

                const button = document.createElement('button');
                button.type = 'button';
                button.className = [
                    'slot-button',
                    slot.time === state.selectedSlot ? 'is-selected' : '',
                    isConfirmedSlot ? 'is-booked-confirmed' : '',
                ].filter(Boolean).join(' ');
                button.textContent = isConfirmedSlot ? `${slot.time} Booked` : slot.time;
                button.disabled = ! slot.available || isConfirmedSlot;
                button.title = isConfirmedSlot ? 'Booked reservation' : (slot.reason || 'Available');

                button.addEventListener('click', async () => {
                    if (! slot.available) {
                        showFeedback('warning', slot.reason || 'Selected time slot is unavailable.');
                        return;
                    }

                    const wasBookingComplete = state.bookingComplete;
                    resetCompletedBooking();
                    state.selectedSlot = slot.time;
                    clearFeedback();

                    if (wasBookingComplete) {
                        await loadSlots();
                    } else {
                        renderSlots(groups);
                        updateSummary();
                    }
                });

                list.appendChild(button);
            });

            groupEl.appendChild(list);
            els.slotGroups.appendChild(groupEl);
        });

        updateSummary();
    }

    function updateSummary() {
        if (state.bookingComplete && state.confirmedReservation) {
            els.summaryDate.textContent = formatDateLabel(state.confirmedReservation.date);
            els.summaryTime.textContent = `${state.confirmedReservation.timeSlot} - Confirmed`;
            els.confirmButton.disabled = false;
            setConfirmButtonLabel('Book Another Appointment');
            return;
        }

        els.summaryDate.textContent = state.selectedDate ? formatDateLabel(state.selectedDate) : 'Select date';
        els.summaryTime.textContent = state.selectedSlot || 'Select time slot';
        els.confirmButton.disabled = ! state.selectedDate || ! state.selectedSlot || state.submitting;
        setConfirmButtonLabel(state.submitting ? 'Confirming...' : 'Confirm Appointment');
    }

    function updateFees() {
        const consultationFee = Number(state.doctor?.consultation_fee ?? 150);
        const processingFee = 5;
        const total = consultationFee + processingFee;

        els.summaryConsultationFee.textContent = formatMoney(consultationFee);
        els.summaryProcessingFee.textContent = formatMoney(processingFee);
        els.summaryTotal.textContent = formatMoney(total);
    }

    async function submitReservation() {
        if (! state.selectedDate || ! state.selectedSlot) {
            showFeedback('warning', 'Please choose both a date and an available time slot.');
            return;
        }

        const patient = getPatientDetails();
        const missingFields = [];

        if (! patient.patient_name) {
            missingFields.push('name');
        }

        if (! patient.patient_email) {
            missingFields.push('email');
        }

        if (! patient.patient_phone) {
            missingFields.push('phone');
        }

        if (missingFields.length) {
            showFeedback('warning', `Please complete patient ${missingFields.join(', ')} before confirming.`);
            return;
        }

        state.submitting = true;
        els.confirmButton.disabled = true;
        setConfirmButtonLabel('Confirming...');

        try {
            const response = await apiRequest('/api/reservations', {
                method: 'POST',
                body: JSON.stringify({
                    doctor_id: state.doctor.id,
                    reservation_date: state.selectedDate,
                    time_slot: state.selectedSlot,
                    ...patient,
                }),
            });

            const reservation = response.data?.reservation;
            const confirmedReservation = {
                code: reservation?.reservation_code || null,
                date: state.selectedDate,
                timeSlot: state.selectedSlot,
            };

            state.confirmedReservation = confirmedReservation;
            state.bookingComplete = true;

            showFeedback(
                'success',
                `Appointment confirmed. Reservation code ${confirmedReservation.code || 'created'} for ${formatDateLabel(confirmedReservation.date)} at ${confirmedReservation.timeSlot}.`
            );
            renderSlots(state.slotGroups);
        } catch (error) {
            showFeedback('warning', error.message || 'Unable to confirm this appointment.');
            await loadSlots();
        } finally {
            state.submitting = false;
            updateSummary();
        }
    }

    function getPatientDetails() {
        return {
            patient_name: els.patientName.value.trim(),
            patient_email: els.patientEmail.value.trim(),
            patient_phone: els.patientPhone.value.trim(),
            notes: els.patientNotes.value.trim(),
        };
    }

    function findDefaultDoctor(doctors) {
        return doctors.find((doctor) => normalizeText(doctor.name) === 'dr. sarah jenkins') || doctors[0];
    }

    function normalizeText(value) {
        return String(value || '').trim().toLowerCase();
    }

    async function startAnotherBooking() {
        resetCompletedBooking();
        state.selectedSlot = null;
        await loadSlots();
    }

    function resetCompletedBooking() {
        if (! state.bookingComplete && ! state.confirmedReservation) {
            return;
        }

        state.bookingComplete = false;
        state.confirmedReservation = null;
        clearFeedback();
        setConfirmButtonLabel('Confirm Appointment');
    }

    function isConfirmedReservationSlot(slotTime) {
        return Boolean(
            state.bookingComplete
            && state.confirmedReservation
            && state.confirmedReservation.date === state.selectedDate
            && state.confirmedReservation.timeSlot === slotTime
        );
    }

    function setConfirmButtonLabel(label) {
        els.confirmButton.innerHTML = `<span class="confirm-button-icon" aria-hidden="true">${confirmIconSvg()}</span>${label}`;
    }

    function confirmIconSvg() {
        return `
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.6 14.6L15.65 7.55L14.25 6.15L8.6 11.8L5.75 8.95L4.35 10.35L8.6 14.6ZM10 20C8.61667 20 7.31667 19.7375 6.1 19.2125C4.88333 18.6875 3.825 17.975 2.925 17.075C2.025 16.175 1.3125 15.1167 0.7875 13.9C0.2625 12.6833 0 11.3833 0 10C0 8.61667 0.2625 7.31667 0.7875 6.1C1.3125 4.88333 2.025 3.825 2.925 2.925C3.825 2.025 4.88333 1.3125 6.1 0.7875C7.31667 0.2625 8.61667 0 10 0C11.3833 0 12.6833 0.2625 13.9 0.7875C15.1167 1.3125 16.175 2.025 17.075 2.925C17.975 3.825 18.6875 4.88333 19.2125 6.1C19.7375 7.31667 20 8.61667 20 10C20 11.3833 19.7375 12.6833 19.2125 13.9C18.6875 15.1167 17.975 16.175 17.075 17.075C16.175 17.975 15.1167 18.6875 13.9 19.2125C12.6833 19.7375 11.3833 20 10 20ZM10 18C12.2333 18 14.125 17.225 15.675 15.675C17.225 14.125 18 12.2333 18 10C18 7.76667 17.225 5.875 15.675 4.325C14.125 2.775 12.2333 2 10 2C7.76667 2 5.875 2.775 4.325 4.325C2.775 5.875 2 7.76667 2 10C2 12.2333 2.775 14.125 4.325 15.675C5.875 17.225 7.76667 18 10 18Z" fill="white"/>
            </svg>
        `;
    }

    function doctorIllustration() {
        return `
            <svg class="doctor-illustration" viewBox="0 0 112 112" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Doctor profile placeholder">
                <rect width="112" height="112" rx="8" fill="#DDF3FB"/>
                <rect x="8" y="8" width="96" height="96" rx="6" fill="url(#doctor-card-bg)"/>
                <path d="M32 103C34.3 83 43.1 71 56 71C68.9 71 77.7 83 80 103H32Z" fill="#F7FAFC"/>
                <path d="M41 77C45 73.3 50 71.4 56 71.4C62 71.4 67 73.3 71 77L64.7 104H47.3L41 77Z" fill="#EEF5FB"/>
                <path d="M49 73L56 82L63 73V64H49V73Z" fill="#D69C73"/>
                <path d="M36 43C36 30.8 44.6 22 56 22C67.4 22 76 30.8 76 43V50C76 62.2 67.4 71 56 71C44.6 71 36 62.2 36 50V43Z" fill="#E7B48A"/>
                <path d="M36 45C39 30 48.4 21.5 61.4 23.2C72.2 24.6 78 33.2 76 47.7C66.6 46.7 57.8 42.9 49.6 36.2C47.4 42 43 45.2 36 45Z" fill="#6F3D20"/>
                <path d="M43 76L56 92L69 76C74.7 82.2 78.3 91.2 80 103H32C33.7 91.2 37.3 82.2 43 76Z" fill="#FFFFFF"/>
                <path d="M49 89L56 98L63 89" stroke="#0070C9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M38 88C32 91 28.2 96 26.5 103H41L45 78C42.2 80.4 39.9 83.7 38 88Z" fill="#FFFFFF"/>
                <path d="M74 88C80 91 83.8 96 85.5 103H71L67 78C69.8 80.4 72.1 83.7 74 88Z" fill="#FFFFFF"/>
                <circle cx="48" cy="48" r="2" fill="#2E2E36"/>
                <circle cx="64" cy="48" r="2" fill="#2E2E36"/>
                <path d="M49 58C52.7 61 59.3 61 63 58" stroke="#8F5131" stroke-width="2" stroke-linecap="round"/>
                <path d="M83 35C90 44 91.8 54.4 88.5 66.3" stroke="#7DCBE6" stroke-width="5" stroke-linecap="round" opacity="0.7"/>
                <defs>
                    <linearGradient id="doctor-card-bg" x1="8" y1="8" x2="104" y2="104" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#BCEAF8"/>
                        <stop offset="1" stop-color="#F3FAFD"/>
                    </linearGradient>
                </defs>
            </svg>
        `;
    }

    async function apiRequest(path, options = {}) {
        const token = localStorage.getItem('token')
            || localStorage.getItem('auth_token')
            || localStorage.getItem('access_token');

        const response = await fetch(path, {
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                ...(options.headers || {}),
            },
            ...options,
        });

        const payload = await response.json().catch(() => ({
            success: false,
            message: 'Unexpected server response.',
            data: {},
        }));

        if (! response.ok || payload.success === false) {
            throw new Error(payload.message || 'Request failed.');
        }

        return payload;
    }

    function shiftDates(days) {
        const nextStart = new Date(state.dateWindowStart);
        nextStart.setDate(nextStart.getDate() + days);

        if (toIsoDate(nextStart) < toIsoDate(startOfToday())) {
            return;
        }

        state.dateWindowStart = nextStart;
        resetCompletedBooking();
        state.selectedDate = toIsoDate(nextStart);
        state.selectedSlot = null;
        renderDates();
        loadSlots();
    }

    function showFeedback(type, message) {
        els.feedback.className = `booking-feedback is-${type}`;
        els.feedback.textContent = message;
    }

    function clearFeedback() {
        els.feedback.className = 'booking-feedback is-hidden';
        els.feedback.textContent = '';
    }

    function startOfToday() {
        const date = new Date();
        date.setHours(12, 0, 0, 0);
        return date;
    }

    function toIsoDate(date) {
        const localDate = new Date(date);
        localDate.setMinutes(localDate.getMinutes() - localDate.getTimezoneOffset());
        return localDate.toISOString().slice(0, 10);
    }

    function formatDateLabel(dateValue) {
        const [year, month, day] = dateValue.split('-').map(Number);
        return new Intl.DateTimeFormat('en', {
            weekday: 'long',
            month: 'short',
            day: 'numeric',
        }).format(new Date(year, month - 1, day));
    }

    function formatMoney(value) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(value);
    }

    function splitLocation(location) {
        if (! location) {
            return {
                primary: 'Main City Hospital',
                secondary: 'Building A, Suite 302',
            };
        }

        const parts = location.split(',').map((part) => part.trim()).filter(Boolean);

        return {
            primary: parts[0] || location,
            secondary: parts.slice(1).join(', ') || location,
        };
    }
});

document.addEventListener('DOMContentLoaded', () => {
    if (document.body.dataset.page !== 'provider-consultation') {
        return;
    }

    const state = {
        consultation: null,
        medications: [],
        saving: false,
    };

    const els = {
        meta: document.getElementById('consultation-meta'),
        status: document.getElementById('consultation-status'),
        feedback: document.getElementById('consultation-feedback'),
        patientAvatar: document.getElementById('patient-avatar'),
        patientName: document.getElementById('patient-name'),
        patientDemographics: document.getElementById('patient-demographics'),
        patientRecord: document.getElementById('patient-record'),
        vitalBlood: document.getElementById('vital-blood'),
        vitalWeight: document.getElementById('vital-weight'),
        vitalBp: document.getElementById('vital-bp'),
        allergyList: document.getElementById('allergy-list'),
        historyList: document.getElementById('history-list'),
        form: document.getElementById('consultation-form'),
        chiefComplaint: document.getElementById('chief-complaint'),
        objectiveObservations: document.getElementById('objective-observations'),
        assessmentPlan: document.getElementById('assessment-plan'),
        medicationList: document.getElementById('medication-list'),
        pharmacyInstructions: document.getElementById('pharmacy-instructions'),
        saveRecords: document.getElementById('save-records'),
        completeSession: document.getElementById('complete-session'),
        addMedication: document.getElementById('add-medication'),
        newConsultationRecord: document.getElementById('new-consultation-record'),
    };

    els.form.addEventListener('submit', (event) => {
        event.preventDefault();
        saveConsultation(false);
    });

    els.completeSession.addEventListener('click', () => saveConsultation(true));
    els.newConsultationRecord.addEventListener('click', () => createNewConsultationRecord());
    els.addMedication.addEventListener('click', () => {
        state.medications.push(blankMedication());
        renderMedicationRows();
        const rows = els.medicationList.querySelectorAll('.prescription-grid');
        rows[rows.length - 1]?.querySelector('input')?.focus();
    });

    document.querySelectorAll('[data-absolute-href]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            window.location.assign(link.dataset.absoluteHref);
        });
    });

    loadConsultation();

    async function loadConsultation() {
        try {
            const params = new URLSearchParams();

            if (document.body.dataset.patientId) {
                params.set('patient_id', document.body.dataset.patientId);
            }

            if (document.body.dataset.doctorId) {
                params.set('doctor_id', document.body.dataset.doctorId);
            }

            const endpoint = `/api/reservations/active-consultation${params.toString() ? `?${params}` : ''}`;
            const response = await apiRequest(endpoint);
            state.consultation = response.data?.consultation;
            renderConsultation();
        } catch (error) {
            showFeedback('warning', error.message || 'Unable to load the active consultation.');
        }
    }

    function renderConsultation() {
        const consultation = state.consultation;
        const patient = consultation.patient;
        const notes = consultation.clinical_notes || {};
        const prescription = consultation.prescription || {};

        els.meta.textContent = `${consultation.header_time} — ${consultation.doctor_name}`;
        els.status.textContent = consultation.status === 'completed' ? 'Session Complete' : 'Session In Progress';
        els.patientName.textContent = patient.name;
        els.patientDemographics.textContent = `DOB: ${formatDob(patient.date_of_birth)} (${patient.age}y) • ${patient.gender?.slice(0, 1) || 'F'}`;
        els.patientRecord.textContent = `ID: ${patient.record_number}`;
        els.vitalBlood.textContent = patient.vitals?.blood_type || 'A+';
        els.vitalWeight.textContent = patient.vitals?.weight || '68 kg';
        els.vitalBp.textContent = patient.vitals?.blood_pressure || '118/76';
        renderAvatar(patient);
        renderAllergies(patient.allergies || []);
        renderHistory(patient.medical_history || []);

        els.chiefComplaint.value = notes.chief_complaint || '';
        els.objectiveObservations.value = notes.objective_observations || '';
        els.assessmentPlan.value = notes.assessment_plan || '';
        state.medications = normalizeMedications(prescription);
        renderMedicationRows();
        els.pharmacyInstructions.value = prescription.pharmacy_instructions || '';

        setButtons(false);
    }

    function renderAvatar(patient) {
        if (patient.avatar) {
            els.patientAvatar.innerHTML = '';
            const image = document.createElement('img');
            image.src = patient.avatar;
            image.alt = patient.name;
            els.patientAvatar.appendChild(image);
            return;
        }

        els.patientAvatar.textContent = initials(patient.name);
    }

    function renderAllergies(allergies) {
        els.allergyList.innerHTML = '';

        allergies.forEach((allergy) => {
            const pill = document.createElement('span');
            pill.className = 'allergy-pill';
            pill.textContent = `⚠ ${allergy}`;
            els.allergyList.appendChild(pill);
        });
    }

    function renderHistory(history) {
        els.historyList.innerHTML = '';

        history.forEach((item) => {
            const article = document.createElement('article');
            article.className = 'history-item';
            article.tabIndex = 0;
            article.setAttribute('role', 'button');
            article.setAttribute('aria-label', `Open ${item.title}`);

            const heading = document.createElement('div');
            const title = document.createElement('strong');
            const date = document.createElement('time');
            title.textContent = item.title;
            date.textContent = formatShortDate(item.date);
            heading.append(title, date);

            const summary = document.createElement('p');
            summary.textContent = item.summary;

            article.append(heading, summary);
            article.addEventListener('click', () => openHistoryItem(item));
            article.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openHistoryItem(item);
                }
            });
            els.historyList.appendChild(article);
        });
    }

    function openHistoryItem(item) {
        const notes = item.clinical_notes || {};
        const prescription = item.prescription || {};

        els.chiefComplaint.value = notes.chief_complaint || '';
        els.objectiveObservations.value = notes.objective_observations || '';
        els.assessmentPlan.value = notes.assessment_plan || item.summary || '';
        state.medications = normalizeMedications(prescription);
        renderMedicationRows();
        els.pharmacyInstructions.value = prescription.pharmacy_instructions || '';
        showFeedback('success', `${item.title} opened in the records panel.`);
    }

    function renderMedicationRows() {
        els.medicationList.innerHTML = '';

        state.medications.forEach((medication, index) => {
            const row = document.createElement('div');
            row.className = 'prescription-grid';
            row.dataset.index = String(index);

            row.append(
                medicationField('Medication Name', 'medication_name', medication.medication_name, 'e.g. Amoxicillin'),
                medicationField('Dosage', 'dosage', medication.dosage, 'e.g. 500mg'),
                frequencyField(medication.frequency),
                removeMedicationButton(index)
            );

            els.medicationList.appendChild(row);
        });
    }

    function medicationField(labelText, key, value, placeholder) {
        const label = document.createElement('label');
        const span = document.createElement('span');
        const input = document.createElement('input');
        span.textContent = labelText;
        input.type = 'text';
        input.value = value || '';
        input.placeholder = placeholder;
        input.addEventListener('input', () => {
            state.medications[Number(input.closest('.prescription-grid').dataset.index)][key] = input.value;
        });
        label.append(span, input);
        return label;
    }

    function frequencyField(value) {
        const label = document.createElement('label');
        const span = document.createElement('span');
        const select = document.createElement('select');
        span.textContent = 'Frequency';

        ['Once daily', 'Twice daily', 'Every 8 hours', 'As needed'].forEach((optionText) => {
            const option = document.createElement('option');
            option.textContent = optionText;
            option.selected = optionText === (value || 'Once daily');
            select.appendChild(option);
        });

        select.addEventListener('change', () => {
            state.medications[Number(select.closest('.prescription-grid').dataset.index)].frequency = select.value;
        });
        label.append(span, select);
        return label;
    }

    function removeMedicationButton(index) {
        const button = document.createElement('button');
        button.className = 'remove-medication';
        button.type = 'button';
        button.setAttribute('aria-label', 'Remove medication');
        button.innerHTML = '&times;';
        button.disabled = state.medications.length === 1;
        button.addEventListener('click', () => {
            state.medications.splice(index, 1);
            renderMedicationRows();
        });
        return button;
    }

    async function saveConsultation(complete) {
        if (! state.consultation || ! state.consultation.id || state.saving) {
            showFeedback('warning', 'The consultation is still loading. Please try again in a moment.');
            return;
        }

        state.saving = true;
        setButtons(true, complete ? 'Completing...' : 'Saving...');

        try {
            const endpoint = complete
                ? `/api/reservations/${state.consultation.id}/complete`
                : `/api/reservations/${state.consultation.id}/consultation`;
            const response = await apiRequest(endpoint, {
                method: 'PATCH',
                body: JSON.stringify(formPayload()),
            });

            state.consultation = response.data?.consultation;
            renderConsultation();
            showFeedback('success', response.message || (complete ? 'Session completed.' : 'Records saved.'));
        } catch (error) {
            showFeedback('warning', error.message || 'Unable to save consultation records.');
        } finally {
            state.saving = false;
            setButtons(false);
        }
    }

    async function createNewConsultationRecord() {
        if (state.saving) {
            return;
        }

        state.saving = true;
        setButtons(true, 'Creating...');

        try {
            const response = await apiRequest('/api/reservations/active-consultation', {
                method: 'POST',
                body: JSON.stringify({
                    patient_id: document.body.dataset.patientId || null,
                    doctor_id: document.body.dataset.doctorId || null,
                }),
            });

            state.consultation = response.data?.consultation;
            renderConsultation();
            showFeedback('success', 'New consultation record is ready.');
        } catch (error) {
            showFeedback('warning', error.message || 'Unable to create a new consultation record.');
        } finally {
            state.saving = false;
            setButtons(false);
        }
    }

    function formPayload() {
        return {
            chief_complaint: els.chiefComplaint.value.trim(),
            objective_observations: els.objectiveObservations.value.trim(),
            assessment_plan: els.assessmentPlan.value.trim(),
            prescription: {
                medications: state.medications.map((medication) => ({
                    medication_name: medication.medication_name.trim(),
                    dosage: medication.dosage.trim(),
                    frequency: medication.frequency || 'Once daily',
                })),
                pharmacy_instructions: els.pharmacyInstructions.value.trim(),
            },
        };
    }

    function setButtons(disabled, label = null) {
        const hasConsultation = Boolean(state.consultation?.id);
        els.saveRecords.disabled = disabled || ! hasConsultation;
        els.completeSession.disabled = disabled || ! hasConsultation || state.consultation?.status === 'completed';
        els.saveRecords.textContent = label && label.startsWith('Saving') ? `Save Records - ${label}` : 'Save Records';
        els.completeSession.textContent = label && label.startsWith('Completing') ? `Mark as Complete - ${label}` : 'Mark as Complete';
    }

    function showFeedback(type, message) {
        els.feedback.className = `consultation-feedback is-${type}`;
        els.feedback.textContent = message;
    }

    async function apiRequest(path, options = {}) {
        const token = localStorage.getItem('token')
            || localStorage.getItem('auth_token')
            || localStorage.getItem('access_token');
        const response = await fetch(path, {
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                ...(options.headers || {}),
            },
            ...options,
        });
        const payload = await response.json().catch(() => ({
            success: false,
            message: 'Unexpected server response.',
            data: {},
        }));

        if (! response.ok || payload.success === false) {
            throw new Error(payload.message || 'Request failed.');
        }

        return payload;
    }

    function initials(name) {
        return String(name || 'Patient')
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0]?.toUpperCase())
            .join('');
    }

    function formatDob(dateValue) {
        if (! dateValue) {
            return '12/04/1978';
        }

        const [year, month, day] = dateValue.split('-');
        return `${month}/${day}/${year}`;
    }

    function formatShortDate(dateValue) {
        if (! dateValue) {
            return '';
        }

        const [year, month, day] = dateValue.split('-').map(Number);
        return new Intl.DateTimeFormat('en', {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
        }).format(new Date(year, month - 1, day));
    }

    function normalizeMedications(prescription) {
        const medications = Array.isArray(prescription.medications)
            ? prescription.medications
            : [];

        const normalized = medications
            .map((medication) => ({
                medication_name: String(medication.medication_name || ''),
                dosage: String(medication.dosage || ''),
                frequency: String(medication.frequency || 'Once daily'),
            }))
            .filter((medication) => medication.medication_name || medication.dosage);

        if (normalized.length) {
            return normalized;
        }

        return [{
            medication_name: String(prescription.medication_name || ''),
            dosage: String(prescription.dosage || ''),
            frequency: String(prescription.frequency || 'Once daily'),
        }];
    }

    function blankMedication() {
        return {
            medication_name: '',
            dosage: '',
            frequency: 'Once daily',
        };
    }
});
