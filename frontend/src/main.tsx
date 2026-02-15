import React from 'react'
import ReactDOM from 'react-dom/client'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { TelegramGrowthPage } from './components/TelegramGrowthPage'
import { CheckoutPage } from './components/CheckoutPage'

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <BrowserRouter>
      <Routes>
        {/* Страница настроек */}
        <Route path="/shops/:shopId/growth/telegram" element={<TelegramGrowthPage />} />
        
        {/* Страница эмуляции заказа */}
        <Route path="/shops/:shopId/checkout" element={<CheckoutPage />} />
        
        {/* Редирект по умолчанию (для теста на shopId 1) */}
        <Route path="*" element={<Navigate to="/shops/1/growth/telegram" replace />} />
      </Routes>
    </BrowserRouter>
  </React.StrictMode>,
)
