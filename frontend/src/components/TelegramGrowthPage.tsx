import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { apiRequest } from '../api/client';
import { TelegramStatus, TelegramLog } from '../types/telegram';
import { ConnectResponse } from '../types/interfaces';

export const TelegramGrowthPage = () => {
  const { shopId } = useParams();
  const [config, setConfig] = useState({ botToken: '', chatId: '', enabled: true });
  const [status, setStatus] = useState<TelegramStatus | null>(null);
  const [logs, setLogs] = useState<TelegramLog[]>([]);
  const [loading, setLoading] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [result, setResult] = useState<any>(null);
  const [globalMessage, setGlobalMessage] = useState<string | null>(null);

  const loadData = async () => {
    try {
      const [s, l] = await Promise.all([
        apiRequest(`/shops/${shopId}/telegram/status`),
        apiRequest(`/shops/${shopId}/telegram/logs`)
      ]);
      setStatus(s.ok ? await s.json() : []);
      setLogs(l.ok ? await l.json() : []);
    } catch (e) {}
  };

  useEffect(() => { loadData(); }, [shopId]);

  const onSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setGlobalMessage('⏳ Сохранение...');
    setFieldErrors({});
    try {
      const response = await apiRequest(`/shops/${shopId}/telegram/connect`, {
        method: 'POST',
        body: JSON.stringify(config)
      });
      const result: ConnectResponse = await response.json();

      if (response.status === 422 && result.violations) {
        const errors: Record<string, string> = {};
        result.violations.forEach(v => { errors[v.propertyPath] ??= v.title; }); // "This value should not be blank" приоритетнее "Chat ID должен состоять из цифр"
        setFieldErrors(errors);
        setGlobalMessage('');
      } else if (!response.ok) {
        setGlobalMessage(result.error || 'Произошла ошибка');
      } else {
        setResult(result);
        setGlobalMessage('✅ Сохранено');
        loadData();
      }
    } catch (e: any) {
      setGlobalMessage(`❌ ${e.message}`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ padding: '20px', maxWidth: '800px', margin: '0 auto' }}>
      <h1>Telegram Интеграция</h1>

      <p>Узнать свой <b>Chat ID</b>: перейдите <a href="https://t.me/getmyid_bot" target="_blank">@Getmyid_bot</a> и нажмите START</p>
      
      <form onSubmit={onSave} style={{ display: 'grid', gap: '10px', marginBottom: '30px' }}>
        <input type="password" placeholder="Bot Token" value={config.botToken} onChange={e => setConfig({...config, botToken: e.target.value})} />
        {fieldErrors.botToken && <span style={{ color: 'red', fontSize: '12px' }}>{fieldErrors.botToken}</span>}

        <input type="text" placeholder="Chat ID" value={config.chatId} onChange={e => setConfig({...config, chatId: e.target.value})} />
        {fieldErrors.chatId && <span style={{ color: 'red', fontSize: '12px' }}>{fieldErrors.chatId}</span>}

        <label><input type="checkbox" checked={config.enabled} onChange={e => setConfig({...config, enabled: e.target.checked})} /> Включено</label>

        <button type="submit" disabled={loading} style={{ flex: 1, padding: '10px', cursor: loading ? 'not-allowed' : 'pointer' }}>
          Сохранить
        </button>
        {globalMessage && <span>{globalMessage}</span>}
        {result && result.bot_info && <a href={"https://t.me/" + result.bot_info.username} title={result.bot_info.first_name}>@{result.bot_info.username}</a>}
      </form>

      {status && (
        <div style={{ background: '#f4f4f4', padding: '15px', borderRadius: '5px' }}>
          <p>Интеграция {status.enabled ? '✅ включена' : '❌ выключена'}</p>
          <h3>Статистика (7 дней)</h3>
          <p>Отправлено: {status.sentCount} | Ошибок: {status.failedCount}</p>
          <p>Последняя активность: {status.lastSentAt || 'нет'}</p>
        </div>
      )}

      <h3>Лог уведомлений</h3>
      <table width="100%">
        <thead>
          <tr>
            <th>Дата</th>
            <th>Статус</th>
            <th>Текст</th>
          </tr>
        </thead>
        <tbody>
          {logs.map(log => (
            <tr key={log.id}>
              <td>{new Date(log.sendAt).toLocaleString()}</td>
              <td style={{ color: log.status === 'SENT' ? 'green' : 'red' }}>{log.status}</td>
              <td>{log.message.split(' ').slice(1).join(' ')}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};
