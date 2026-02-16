export interface TelegramConnectPayload {
  botToken: string;
  chatId: string;
  enabled: boolean;
}

interface Order {
  id: number;
  number: string;
  total: number;
}

interface Violation {
  propertyPath: string;
  title: string;
}

export interface ConnectResponse {
  status: string;
  violations?: Violation[]; // Ошибки валидации Symfony
  bot_info?: {
    id: number;
    first_name: string;
    username: string;
  };
  error?: string; // Глобальная ошибка (например, "Shop not found")
}

// Специфическая ошибка валидации Symfony
interface ValidationError {
  status: 422;
  title: string;
  detail: string;
  violations: Violation[];
  type: string;
}

export type OrderResponse = 
  | { order: Order; deliveryStatus: string; error?: never; status?: never } // Успех
  | { error: string; status: 404; order?: never }                          // Ошибка (404)
  | ValidationError & { order?: never; error?: never };                   // Ошибка валидации (422)