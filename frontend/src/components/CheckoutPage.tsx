import React, { useState } from 'react';
import { useParams } from 'react-router-dom';
import { apiRequest } from '../api/client';

import { ConnectResponse, OrderResponse } from '../types/interfaces';

export const CheckoutPage = () => {
  const { shopId } = useParams();
  const [orderInfo, setOrderInfo] = useState({ 
    number: `A-1${Math.floor(Math.random() * 1000)}`, 
    total: String(Math.floor(Math.random() * 9000) + 1000), 
    customerName: '' 
  });
  const [result, setResult] = useState<any>(null);

  const [loading, setLoading] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [globalMessage, setGlobalMessage] = useState<string | null>(null);

  const handlePlaceOrder = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {

      const response = await apiRequest(`/shops/${shopId}/orders`, {
        method: 'POST',
        body: JSON.stringify(orderInfo),
      });

      const result: OrderResponse = await response.json();

      if (result.status === 422 && result.violations) {
        const errors: Record<string, string> = {};
        result.violations.forEach(v => { errors[v.propertyPath] = v.title; });
        setFieldErrors(errors);
      } else if (!response.ok) {
        setGlobalMessage(result.error || 'Произошла ошибка');
      } else {
        setResult(result);
      }

      // setResult(data);
    } catch (e: any) {
      alert(e.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ padding: '20px' }}>
      <h2>🛒 Оформление заказа</h2>
      <form onSubmit={handlePlaceOrder}>
        <input placeholder="Ваше имя" required 
          onChange={e => setOrderInfo({...orderInfo, customerName: e.target.value})} />
        {fieldErrors.customerName && <span style={{ color: 'red', fontSize: '12px' }}>{fieldErrors.customerName}</span>}

        <p>Сумма к оплате: <b>{orderInfo.total} ₽</b></p>

        <button type="submit" disabled={loading}>
          {loading ? 'Подождите...' : 'Оплатить и заказать'}
        </button>

        {globalMessage && <p>{globalMessage}</p>}
      </form>

      {result && (
        <div style={{ marginTop: '20px', padding: '10px', background: '#eee' }}>
          <h4>Результат:</h4>
          <p>Заказ: #{result.order.number}</p>
          <p>Статус уведомления: <b>{result.deliveryStatus}</b></p>
        </div>
      )}
    </div>
  );
};
