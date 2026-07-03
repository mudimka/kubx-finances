<?php

namespace Sprint\Migration;


class CEVENT_ORDER_REGISTRATION_09_10_25_20251013095121 extends Version
{
    protected $author = "admin";

    protected $description = "";

    protected $moduleVersion = "5.4.1";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Event()->saveEventType('ORDER_REGISTRATION', array (
  'LID' => 'ru',
  'EVENT_TYPE' => 'email',
  'NAME' => 'Регистрация во время оформления заказа',
  'DESCRIPTION' => '#PHONE#
#PASSWORD#',
  'SORT' => '150',
));
            $helper->Event()->saveEventType('ORDER_REGISTRATION', array (
  'LID' => 'en',
  'EVENT_TYPE' => 'email',
  'NAME' => 'Регистрация во время оформления заказа',
  'DESCRIPTION' => '',
  'SORT' => '150',
));
            $helper->Event()->saveEventMessage('ORDER_REGISTRATION', array (
  'LID' => 
  array (
    0 => 's1',
  ),
  'ACTIVE' => 'Y',
  'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
  'EMAIL_TO' => '#EMAIL#',
  'SUBJECT' => '#SITE_NAME#: Вы зарегистрированы в системе',
  'MESSAGE' => '<table
  style="
    width: 600px;
    margin: 0 auto;
    padding-top: 30px;
    padding-bottom: 30px;
    padding-left: 10px;
    padding-right: 10px;
  "
>
  <tbody>
    <tr>
      <td>
        <table
          style="
            background-color: white;
            width: 100%;
            border-radius: 32px;
            padding: 30px;
            box-sizing: border-box;
          "
        >
          <tbody>
            <tr>
              <td>
                <img
                  style="
                    width: 100%;
                    box-shadow: 0px 25px 50px -12px #00000040;
                    border-radius: 24px;
                  "
                  src="http://kubx-dev.devaid.ru/upload/email_images_new/header.png"
                />
              </td>
            </tr>
            <tr>
              <td style="padding-top: 15px">
                <p style="color: #444444; font-size: 16px; line-height: 1.4">
                  Добро пожаловать в KUBX.B2B! Ваш пароль:
                </p>
              </td>
            </tr>
            <tr>
              <td style="padding-top: 30px">
                <div
                  style="
                    width: 100%;
                    height: 90px;
                    background-image: url(\'http://kubx-dev.devaid.ru/upload/email_images_new/bg_code.png\');
                    background-repeat: repeat;
                    background-size: 7%;
                    border-radius: 12px;
                    color: #1c64f2;
                  "
                >
                  <p
                    style="
                      padding-top: 22px;
                      text-align: center;
                      font-weight: 500;
                      font-size: 36px;
                      margin: 0 auto;
                      font-size: 36px;
                    "
                  >
                    #PASSWORD#
                  </p>
                </div>
              </td>
            </tr>
            <tr>
              <td style="padding-top: 15px; padding-bottom: 15px">
                <p style="color: #444444; font-size: 16px; line-height: 1.4">
                  Код подтверждения действителен только в течение 15 минут.
                  Пожалуйста, не сообщайте этот код никому
                </p>
              </td>
            </tr>
            <tr>
              <td style="padding-bottom: 30px">
                <p
                  style="
                    padding-top: 40px;
                    padding-bottom: 40px;
                    padding-left: 30px;
                    padding-right: 30px;
                    background-color: #ebf5ff;
                    font-size: 16px;
                    line-height: 1.5;
                  "
                >
                  Внимание! Если вы не инициировали этот запрос, немедленно
                  обратитесь в службу поддержки
                </p>
              </td>
            </tr>

            <!-- cards  -->
            <tr>
              <td>
                <!-- cards table -->
                <table>
                  <tbody>
                    <tr>
                      <td
                        style="
                          width: 50%;
                          align-content: start;
                          padding-right: 10px;
                        "
                      >
                        <!-- card -->
                        <table>
                          <tbody>
                            <tr>
                              <td>
                                <img
                                  style="display: block; width: 100%"
                                  src="http://kubx-dev.devaid.ru/upload/email_images_new/platform.png"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 14px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #222222;
                                    font-size: 16px;
                                    line-height: 1.2;
                                    padding-bottom: 7px;
                                    padding: 0;
                                  "
                                >
                                  <strong style="word-break: break-word">
                                    Экосистема готовых решений
                                  </strong>
                                </p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 8px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #6b7280;
                                    font-size: 12px;
                                    line-height: 1.45;
                                  "
                                >
                                  Платформа, которая позволяет вам легко
                                  создавать и управлять своим интернет-магазином
                                </p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                      <td align="start" style="width: 50%; padding-left: 10px">
                        <!-- card -->
                        <table>
                          <tbody>
                            <tr>
                              <td>
                                <img
                                  style="display: block; width: 100%"
                                  src="http://kubx-dev.devaid.ru/upload/email_images_new/autoload.png"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 14px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #222222;
                                    font-size: 16px;
                                    line-height: 1.2;
                                    padding-bottom: 7px;
                                    padding: 0;
                                  "
                                >
                                  <strong style="word-break: break-word">
                                    Автозагрузка товаров
                                  </strong>
                                </p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 8px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #6b7280;
                                    font-size: 12px;
                                    line-height: 1.45;
                                  "
                                >
                                  Авто-загрузка товаров на витрины экономит
                                  время и уменьшает количество ошибок
                                </p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>

                    <tr>
                      <td
                        style="
                          width: 50%;
                          align-content: start;
                          padding-right: 10px;
                          padding-top: 36px;
                        "
                      >
                        <!-- card -->
                        <table>
                          <tbody>
                            <tr>
                              <td>
                                <img
                                  style="display: block; width: 100%"
                                  src="http://kubx-dev.devaid.ru/upload/email_images_new/systems.png"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 14px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #222222;
                                    font-size: 16px;
                                    line-height: 1.2;
                                    padding-bottom: 7px;
                                    padding: 0;
                                  "
                                >
                                  <strong style="word-break: break-word">
                                    Интеграции с любыми системами
                                  </strong>
                                </p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 8px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #6b7280;
                                    font-size: 12px;
                                    line-height: 1.45;
                                  "
                                >
                                  С любыми информационными системами.
                                  Интеграционная шина и REST API из коробки.
                                </p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                      <td
                        style="
                          width: 50%;
                          align-content: start;
                          padding-left: 10px;
                          padding-top: 36px;
                        "
                      >
                        <!-- card -->
                        <table>
                          <tbody>
                            <tr>
                              <td>
                                <img
                                  style="display: block; width: 100%"
                                  src="http://kubx-dev.devaid.ru/upload/email_images_new/lk.png"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 14px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #222222;
                                    font-size: 16px;
                                    line-height: 1.2;
                                    padding-bottom: 7px;
                                    padding: 0;
                                  "
                                >
                                  <strong style="word-break: break-word">
                                    Личный кабинет под каждую роль
                                  </strong>
                                </p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 8px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #6b7280;
                                    font-size: 12px;
                                    line-height: 1.45;
                                  "
                                >
                                  Гибкая настройка прав. Каждый пользователь
                                  видит только нужные ему функционал
                                </p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>

                    <tr>
                      <td
                        style="
                          align-content: start;
                          padding-right: 10px;
                          padding-top: 36px;
                        "
                      >
                        <!-- card -->
                        <table>
                          <tbody>
                            <tr>
                              <td>
                                <img
                                  style="display: block; width: 100%"
                                  src="http://kubx-dev.devaid.ru/upload/email_images_new/ecom.png"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 14px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #222222;
                                    font-size: 16px;
                                    line-height: 1.2;
                                    padding-bottom: 7px;
                                    padding: 0;
                                  "
                                >
                                  <strong style="word-break: break-word">
                                    Связь с e-com системами
                                  </strong>
                                </p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 8px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #6b7280;
                                    font-size: 12px;
                                    line-height: 1.45;
                                  "
                                >
                                  Экосистема продуктов (PIM, e-Com, ЛК, CRM
                                  Битрикс24) бесшовно связаны между собой
                                </p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                      <td
                        style="
                          align-content: start;
                          padding-left: 10px;
                          padding-top: 36px;
                        "
                      >
                        <!-- card -->
                        <table>
                          <tbody>
                            <tr>
                              <td>
                                <img
                                  style="display: block; width: 100%"
                                  src="http://kubx-dev.devaid.ru/upload/email_images_new/adapt.png"
                                />
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 14px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #222222;
                                    font-size: 16px;
                                    line-height: 1.2;
                                    padding-bottom: 7px;
                                    padding: 0;
                                  "
                                >
                                  <strong style="word-break: break-word">
                                    Адаптируемость под бизнес
                                  </strong>
                                </p>
                              </td>
                            </tr>
                            <tr>
                              <td style="padding-top: 8px">
                                <p
                                  style="
                                    font-weight: normal;
                                    color: #6b7280;
                                    font-size: 12px;
                                    line-height: 1.45;
                                  "
                                >
                                  Богатая функциональность из коробки, легко
                                  кастомизируется под любые задачи
                                </p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <!-- cards table -->
            </tr>
            <tr>
              <td style="padding-top: 15px; padding-bottom: 15px">
                <div style="height: 1px; background-color: #dedede"></div>
              </td>
            </tr>
            <tr>
              <td style="padding-top: 15px; padding-bottom: 15px">
                <p
                  style="
                    margin-right: auto;
                    color: #111928;
                    font-size: 12px;
                    line-height: 1.45;
                  "
                >
                  КУБИКС - Это модульная экосистема готовых решений для
                  электронной коммерции<br />Программа предназначена для
                  быстрого запуска и масштабирования интернет-магазинов, нишевых
                  маркетплейсов, B2B-платформ, клиентских кабинетов, лендингов и
                  конфигураторов товаров.<br /><br />Платформа обеспечивает
                  автоматизацию ключевых бизнес-процессов между производителями,
                  дистрибьюторами и их контрагентами: от оформления и обработки
                  заказов до логистики, документооборота и управления
                  маркетинговыми активностями. <br /><br />Покупатели получают
                  удобный личный кабинет с возможностью отслеживания заказов,
                  общения с менеджерами и контроля дебиторской задолженности.
                  Продавцы — инструменты для управления каталогом, заказами и
                  интеграции с ERP и CRM-системами. <br /><br />Гибкая модульная
                  архитектура позволяет адаптировать решение под любую
                  отраслевую специфику, обеспечивая персонализацию и высокую
                  скорость внедрения.
                </p>
              </td>
            </tr>

            <tr>
              <td style="padding-top: 30px; padding-bottom: 15px">
                <div
                  style="
                    color: #1a56db;
                    font-size: 12px;
                    line-height: 1.3;
                    text-align: center;
                  "
                >
                  <strong style="color: rgb(255, 255, 255)">
                    <a
                      href="mailto:info@legacystudio.ru"
                      style="color: rgb(26, 86, 219)"
                      target="_blank"
                      rel="noreferrer noopener"
                      >Поддержка
                    </a>
                    ✦
                  </strong>
                  <strong>✦</strong>
                  <strong style="color: rgb(255, 255, 255)">✦</strong>
                  <strong>
                    <a
                      href="https://kubx.tech/"
                      target="_blank"
                      rel="noreferrer noopener"
                      style="color: rgb(26, 86, 219)"
                      >На сайт</a
                    >
                  </strong>
                  <strong style="color: rgb(255, 255, 255)">✦</strong>
                  <strong>✦</strong>
                  <strong style="color: rgb(255, 255, 255)">✦</strong>
                  <strong>
                    <a
                      href="https://t.me/Legatys"
                      target="_blank"
                      rel="noreferrer noopener"
                      style="color: rgb(26, 86, 219)"
                      >Отдел продаж</a
                    >
                  </strong>
                </div>
              </td>
            </tr>
            <tr>
              <td style="padding-top: 15px" align="center">
                <a style="text-decoration: none" href="https://t.me/kubx_tech">
                  <img
                    style="display: block; width: 44px"
                    src="http://kubx-dev.devaid.ru/upload/email_images_new/tg.png"
                  />
                </a>
              </td>
            </tr>

            <tr>
              <td
                style="padding-top: 15px; padding-bottom: 15px"
                align="center"
              >
                <p style="font-size: 12px; color: #6b7280; text-align: center">
                  Это письмо сгенерировано автоматически. Пожалуйста, не
                  отвечайте на него — ваш ответ не будет прочитан и не дойдёт до
                  нашей команды. Если у вас есть вопросы, обратитесь в службу
                  поддержки через личный кабинет или по контактам, указанным на
                  сайте.
                </p>
              </td>
            </tr>
          </tbody>
        </table>
        <table style="width: 100%; box-sizing: border-box">
          <tr>
            <td style="padding-top: 15px">
              <p style="text-align: center; color: #ffffff">
                © 2025 DEMO.KUBX.TECH
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </tbody>
</table>
',
  'BODY_TYPE' => 'html',
  'BCC' => '',
  'REPLY_TO' => '',
  'CC' => '',
  'IN_REPLY_TO' => '',
  'PRIORITY' => '',
  'FIELD1_NAME' => '',
  'FIELD1_VALUE' => '',
  'FIELD2_NAME' => '',
  'FIELD2_VALUE' => '',
  'SITE_TEMPLATE_ID' => 'kubx_template',
  'ADDITIONAL_FIELD' => 
  array (
  ),
  'LANGUAGE_ID' => 'ru',
  'EVENT_TYPE' => '[ ORDER_REGISTRATION ] Регистрация во время оформления заказа',
));
        }
}
