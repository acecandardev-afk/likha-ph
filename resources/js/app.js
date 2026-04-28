import './bootstrap';
import { initAdminSalesForm } from './pages/admin-sales-form';
import { initChatModal, initChatPage } from './pages/chat';
import { initLikhaUi } from './likha-ui';
import { initLikhaFlashToasts, showLikhaToast } from './flash-toasts';
import { initLocationSelectors, initCheckoutAddressForm, initGuihulnganCheckoutForm } from './pages/location-selectors';

window.initLocationSelectors = initLocationSelectors;
window.initCheckoutAddressForm = initCheckoutAddressForm;
window.initGuihulnganCheckoutForm = initGuihulnganCheckoutForm;
window.initLikhaFlashToasts = initLikhaFlashToasts;
window.showLikhaToast = showLikhaToast;

document.addEventListener('DOMContentLoaded', () => {
    initLikhaFlashToasts();
    initLikhaUi();
    initAdminSalesForm();
    initChatPage();
    initChatModal();
});
