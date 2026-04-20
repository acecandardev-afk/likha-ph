import './bootstrap';
import { initAdminSalesForm } from './pages/admin-sales-form';
import { initChatModal, initChatPage } from './pages/chat';
import { initLikhaUi } from './likha-ui';

document.addEventListener('DOMContentLoaded', () => {
    initLikhaUi();
    initAdminSalesForm();
    initChatPage();
    initChatModal();
});
