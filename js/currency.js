// js/currency.js
const CURRENCY_API = 'https://api.exchangerate-api.com/v4/latest/USD';
const DEFAULT_CURRENCY = 'USD';

// Available currencies
const SUPPORTED_CURRENCIES = ['USD', 'EUR', 'GBP', 'KES', 'INR', 'AUD', 'CAD', 'JPY'];

// Currency Symbols mapped
const CURRENCY_SYMBOLS = {
    'USD': '$',
    'EUR': '€',
    'GBP': '£',
    'KES': 'KSh ',
    'INR': '₹',
    'AUD': 'A$',
    'CAD': 'C$',
    'JPY': '¥'
};

async function fetchExchangeRates() {
    try {
        // Check cache first to avoid hitting API limits
        const cached = localStorage.getItem('exchangeRates');
        const cacheTime = localStorage.getItem('exchangeRatesTime');
        const now = new Date().getTime();

        // Cache valid for 24 hours
        if (cached && cacheTime && (now - parseInt(cacheTime) < 24 * 60 * 60 * 1000)) {
            return JSON.parse(cached);
        }

        const response = await fetch(CURRENCY_API);
        const data = await response.json();

        localStorage.setItem('exchangeRates', JSON.stringify(data.rates));
        localStorage.setItem('exchangeRatesTime', now.toString());

        return data.rates;
    } catch (error) {
        console.error('Failed to fetch exchange rates:', error);
        // Fallback to 1:1 if API fails
        return {};
    }
}

function formatPrice(amount, currency) {
    const symbol = CURRENCY_SYMBOLS[currency] || currency + ' ';

    // Formatting logic (e.g. no decimals for JPY or KES)
    const options = {
        minimumFractionDigits: (currency === 'JPY' || currency === 'KES') ? 0 : 2,
        maximumFractionDigits: (currency === 'JPY' || currency === 'KES') ? 0 : 2
    };

    return symbol + amount.toLocaleString('en-US', options);
}

async function updatePricesAcrossSite() {
    const selectedCurrency = localStorage.getItem('selectedCurrency') || DEFAULT_CURRENCY;
    const rates = await fetchExchangeRates();
    const rate = rates[selectedCurrency] || 1;

    // Find all elements with data-usd attribute
    const priceElements = document.querySelectorAll('[data-usd]');

    priceElements.forEach(el => {
        const usdValue = parseFloat(el.getAttribute('data-usd'));
        if (!isNaN(usdValue)) {
            const convertedValue = usdValue * rate;
            el.textContent = formatPrice(convertedValue, selectedCurrency);
        }
    });
}

function handleCurrencyChange(e) {
    const newCurrency = e.target.value;
    localStorage.setItem('selectedCurrency', newCurrency);
    updatePricesAcrossSite();

    // Update all selectors on page if multiple exist
    document.querySelectorAll('.currency-selector').forEach(sel => {
        sel.value = newCurrency;
    });
}

// Inject currency selector into the nav automatically if container exists, or manual placement
function setupCurrencySelectors() {
    const selectedCurrency = localStorage.getItem('selectedCurrency') || DEFAULT_CURRENCY;

    document.querySelectorAll('.currency-selector').forEach(selector => {
        // Only populate if empty
        if (selector.options.length === 0) {
            SUPPORTED_CURRENCIES.forEach(curr => {
                const opt = document.createElement('option');
                opt.value = curr;
                opt.textContent = curr;
                if (curr === selectedCurrency) opt.selected = true;
                selector.appendChild(opt);
            });
        }

        selector.value = selectedCurrency;
        selector.addEventListener('change', handleCurrencyChange);
    });
}

// Initialize on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setupCurrencySelectors();
        updatePricesAcrossSite();
    });
} else {
    setupCurrencySelectors();
    updatePricesAcrossSite();
}
