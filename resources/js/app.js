import './bootstrap';
import { initAdminSalesForm } from './pages/admin-sales-form';
import { initChatModal, initChatPage } from './pages/chat';
import { initLikhaUi } from './likha-ui';
import { initLocationSelectors, initCheckoutAddressForm, initGuihulnganCheckoutForm } from './pages/location-selectors';

window.initLocationSelectors = initLocationSelectors;
window.initCheckoutAddressForm = initCheckoutAddressForm;
window.initGuihulnganCheckoutForm = initGuihulnganCheckoutForm;

document.addEventListener('DOMContentLoaded', () => {
    initLikhaUi();
    initAdminSalesForm();
    initChatPage();
    initChatModal();
});
