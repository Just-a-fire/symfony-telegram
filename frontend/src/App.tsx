import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { TelegramGrowthPage } from './components/TelegramGrowthPage';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/shops/:shopId/growth/telegram" element={<TelegramGrowthPage />} />
      </Routes>
    </BrowserRouter>
  );
}
