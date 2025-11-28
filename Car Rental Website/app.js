// **********************************************
// --- 1. MODAL AND AUTHENTICATION LOGIC ---
// **********************************************

// --- Modal Elements ---
const modalContainer = document.getElementById('auth-modal');
const userAuthTrigger = document.getElementById('user-auth-trigger');
const closeModal = document.getElementById('close-modal');

// --- Form Elements ---
const loginForm = document.getElementById('login-form');
const signupForm = document.getElementById('signup-form');
const showSignupLink = document.getElementById('show-signup');
const showLoginLink = document.getElementById('show-login');

// --- Function to open the modal ---
if (userAuthTrigger && modalContainer && loginForm && signupForm) {
    userAuthTrigger.addEventListener('click', (e) => {
        e.preventDefault();
        modalContainer.style.display = 'flex';
        // Reset to login form whenever the modal opens
        loginForm.classList.remove('hidden');
        signupForm.classList.add('hidden');
    });
}

// --- Function to close the modal (X button) ---
if (closeModal && modalContainer) {
    closeModal.addEventListener('click', () => {
        modalContainer.style.display = 'none';
    });
}

// --- Function to close the modal (clicking outside) ---
if (modalContainer) {
    window.addEventListener('click', (event) => {
        if (event.target === modalContainer) {
            modalContainer.style.display = 'none';
        }
    });
}

// --- Switch to Signup Form ---
if (showSignupLink && loginForm && signupForm) {
    showSignupLink.addEventListener('click', (e) => {
        e.preventDefault();
        loginForm.classList.add('hidden');
        signupForm.classList.remove('hidden');
    });
}

// --- Switch to Login Form ---
if (showLoginLink && signupForm && loginForm) {
    showLoginLink.addEventListener('click', (e) => {
        e.preventDefault();
        signupForm.classList.add('hidden');
        loginForm.classList.remove('hidden');
    });
}


// **********************************************
// --- 2. MOBILE MENU TOGGLE LOGIC ---
// **********************************************
const menuIcon = document.querySelector('.menu-icon');
const navbar = document.querySelector('.navbar');

if (menuIcon && navbar) {
    menuIcon.addEventListener('click', () => {
        navbar.classList.toggle('active');
        menuIcon.classList.toggle('move');
    });
}


// **********************************************
// --- 3. CAR SEARCH, FILTERING, & AUTOCOMPLETE LOGIC ---
// **********************************************

const searchInput = document.getElementById('car-search-input');
const searchButton = document.getElementById('search-button');
const carListContainer = document.getElementById('car-list-container');
const autocompleteResults = document.getElementById('autocomplete-results');

if (searchInput && carListContainer) {
    // 1. Get all car items and their names for filtering/suggestions
    const carItems = document.querySelectorAll('.car-item');
    let carNames = [];
    carItems.forEach(item => {
        const name = item.getAttribute('data-name');
        if (name) {
            carNames.push(name.toLowerCase());
        }
    });
    // Remove duplicates
    carNames = [...new Set(carNames)];


    // 2. Main filtering function: Hides/shows car boxes
    const filterCars = (query) => {
        const lowerCaseQuery = query.toLowerCase().trim();
        carItems.forEach(item => {
            const carName = item.getAttribute('data-name').toLowerCase();
            if (carName.includes(lowerCaseQuery)) {
                item.style.display = 'block'; // Show car
            } else {
                item.style.display = 'none'; // Hide car
            }
        });
    };

    // 3. Autocomplete function: Displays matching suggestions
    const updateAutocomplete = (query) => {
        autocompleteResults.innerHTML = ''; // Clear previous results
        const trimmedQuery = query.toLowerCase().trim();
        
        // Only show suggestions if the query has at least 2 characters
        if (trimmedQuery.length < 2) return; 

        // Match names that START with the query
        const matchingNames = carNames.filter(name => name.startsWith(trimmedQuery));

        matchingNames.slice(0, 5).forEach(name => {
            const listItem = document.createElement('li');
            // Display name with correct capitalization
            listItem.textContent = name.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join('-');
            
            listItem.style.padding = '10px 15px';
            listItem.style.cursor = 'pointer';
            listItem.style.borderBottom = '1px solid #eee';
            listItem.style.listStyle = 'none'; // Ensure list style is removed

            listItem.addEventListener('click', () => {
                searchInput.value = listItem.textContent;
                filterCars(listItem.textContent);
                autocompleteResults.innerHTML = ''; // Clear suggestions after selection
            });

            autocompleteResults.appendChild(listItem);
        });
    };

    // 4. Event listener for real-time search and autocomplete
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value;
        filterCars(query);
        updateAutocomplete(query);
    });

    // 5. Event listener for search button click (applies filter)
    if (searchButton) {
        searchButton.addEventListener('click', () => {
            filterCars(searchInput.value);
            autocompleteResults.innerHTML = '';
        });
    }

    // 6. Event listener for Enter key in search input (applies filter)
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent form submission
            filterCars(searchInput.value);
            autocompleteResults.innerHTML = '';
        }
    });
    
    // 7. Close suggestions when clicking away from the search box
    document.addEventListener('click', (e) => {
        if (searchInput && autocompleteResults && !searchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
            autocompleteResults.innerHTML = '';
        }
    });
}