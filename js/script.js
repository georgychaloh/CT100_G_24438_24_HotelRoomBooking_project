
/* ==========================================
   HOTEL ROOM BOOKING AND BILLING SYSTEM
   JavaScript validation and bill calculation
   ========================================== */

document.addEventListener("DOMContentLoaded", function () {

    // Find the booking form and its fields
    const form = document.getElementById("booking-form");

    // If this page has no booking form, do nothing
    if (!form) return;

    const name = document.getElementById("full_name");
    const email = document.getElementById("email");
    const phone = document.getElementById("phone");
    const nights = document.getElementById("nights");
    const guests = document.getElementById("guests");
    const roomType = document.getElementById("room_type");

    // Room prices per night (KSh)
    const roomPrices = {
        single: 3000,
        double: 5000,
        family: 8000
    };

    // Optional service prices (KSh)
    const breakfastPrice = 700;
    const airportTransferPrice = 2000;
    const conferenceRoomPrice = 5000;

    // Display validation error messages
    function showError(field, message) {
        const errorElement = document.getElementById(field.id + "_error");

        if (errorElement) {
            errorElement.textContent = message;
        }

        field.setAttribute("aria-invalid", "true");
    }

    // Clear a validation error
    function clearError(field) {
        const errorElement = document.getElementById(field.id + "_error");

        if (errorElement) {
            errorElement.textContent = "";
        }

        field.removeAttribute("aria-invalid");
    }

    // Validate all required fields
    function validateForm() {
        let valid = true;

        const fields = [name, email, phone, nights, guests, roomType];

        fields.forEach(function (field) {
            if (field) clearError(field);
        });

        // Validate customer name
        if (!name || name.value.trim() === "") {
            if (name) showError(name, "Customer name is required.");
            valid = false;
        }

        // Validate email address
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email || email.value.trim() === "") {
            if (email) showError(email, "Email address is required.");
            valid = false;
        } else if (!emailPattern.test(email.value.trim())) {
            showError(email, "Please enter a valid email address.");
            valid = false;
        }

        // Validate exactly 10 digits
        if (!phone || phone.value.trim() === "") {
            if (phone) showError(phone, "Phone number is required.");
            valid = false;
        } else if (!/^\d{10}$/.test(phone.value.trim())) {
            showError(phone, "Phone number must contain exactly 10 digits.");
            valid = false;
        }

        // Validate nights: 1 to 14
        const nightCount = nights ? Number(nights.value) : 0;

        if (!nights || nights.value.trim() === "" ||
            !Number.isInteger(nightCount) ||
            nightCount < 1 || nightCount > 14) {
            if (nights) {
                showError(nights, "Number of nights must be between 1 and 14.");
            }
            valid = false;
        }

        // Validate guests: more than zero
        const guestCount = guests ? Number(guests.value) : 0;

        if (!guests || guests.value.trim() === "" ||
            !Number.isInteger(guestCount) || guestCount <= 0) {
            if (guests) {
                showError(guests, "Number of guests must be greater than zero.");
            }
            valid = false;
        }

        // Validate room selection
        if (!roomType || roomType.value === "") {
            if (roomType) {
                showError(roomType, "Please select a room type.");
            }
            valid = false;
        }

        // Focus the first invalid field
        if (!valid) {
            const firstInvalid = fields.find(function (field) {
                return field &&
                    field.getAttribute("aria-invalid") === "true";
            });

            if (firstInvalid) firstInvalid.focus();
        }

        return valid;
    }

    // Safely display a calculated amount
    function displayAmount(id, amount) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = "KSh " + amount.toLocaleString("en-KE");
        }
    }

    // Calculate the estimated booking bill
    function updateBill() {
        const selectedRoom = roomType ? roomType.value : "";
        const nightCount = nights ? Number(nights.value) || 0 : 0;
        const guestCount = guests ? Number(guests.value) || 0 : 0;

        const roomCost =
            (roomPrices[selectedRoom] || 0) * nightCount;

        const breakfastCheckbox =
            document.getElementById("breakfast");

        const transferCheckbox =
            document.getElementById("airport_transfer");

        const conferenceCheckbox =
            document.getElementById("conference_room");

        const breakfastCost =
            breakfastCheckbox && breakfastCheckbox.checked
                ? breakfastPrice * guestCount * nightCount
                : 0;

        const transferCost =
            transferCheckbox && transferCheckbox.checked
                ? airportTransferPrice
                : 0;

        const conferenceCost =
            conferenceCheckbox && conferenceCheckbox.checked
                ? conferenceRoomPrice
                : 0;

        const total =
            roomCost + breakfastCost + transferCost + conferenceCost;

        displayAmount("room-cost", roomCost);
        displayAmount("breakfast-cost", breakfastCost);
        displayAmount("transfer-cost", transferCost);
        displayAmount("conference-cost", conferenceCost);
        displayAmount("total-cost", total);
    }

    // Prevent submission when required entries are invalid
    form.addEventListener("submit", function (event) {
        if (!validateForm()) {
            event.preventDefault();
        }
    });

    // Clear errors as the customer corrects entries
    [name, email, phone, nights, guests, roomType]
        .filter(Boolean)
        .forEach(function (field) {
            field.addEventListener("input", function () {
                clearError(field);
                updateBill();
            });

            field.addEventListener("change", function () {
                clearError(field);
                updateBill();
            });
        });

    // Recalculate the bill when optional services change
    ["breakfast", "airport_transfer", "conference_room"]
        .forEach(function (id) {
            const checkbox = document.getElementById(id);

            if (checkbox) {
                checkbox.addEventListener("change", updateBill);
            }
        });

    // Initialize the bill when the page opens
    updateBill();

});
