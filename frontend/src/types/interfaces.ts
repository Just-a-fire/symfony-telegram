export interface TelegramConnectPayload {
  botToken: string;
  chatId: string;
  enabled: boolean;
}

interface ValidationError {
  propertyPath: string;
  title: string;
}

export interface ConnectResponse {
  status: string;
  violations?: ValidationError[]; // Ошибки валидации Symfony
  bot_info?: {
    id: number;
    first_name: string;
    username: string;
  };
  error?: string; // Глобальная ошибка (например, "Shop not found")
}