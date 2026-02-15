export interface TelegramStatus {
  enabled: boolean;
  chatId: string;
  lastSentAt: string | null;
  sentCount: number;
  failedCount: number;
}

export interface TelegramLog {
  id: number;
  message: string;
  status: 'SENT' | 'FAILED';
  sendAt: string;
}
