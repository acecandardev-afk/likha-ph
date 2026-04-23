import './bootstrap';
import { initAdminSalesForm } from './pages/admin-sales-form';
import { initChatModal, initChatPage } from './pages/chat';
import { initLikhaUi } from './likha-ui';
import { initLocationSelectors, initCheckoutAddressForm } from './pages/location-selectors';

window.initLocationSelectors = initLocationSelectors;
window.initCheckoutAddressForm = initCheckoutAddressForm;

document.addEventListener('DOMContentLoaded', () => {
    initLikhaUi();
    initAdminSalesForm();
    initChatPage();
    initChatModal();
});
