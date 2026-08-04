<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyWidget\Plugin\ShopUi;

use Spryker\Yves\Kernel\Widget\AbstractWidgetPlugin;
use SprykerShop\Yves\CompanyWidget\Widget\CompanyMenuItemWidget;

/**
 * @deprecated Use {@link \SprykerShop\Yves\CompanyWidget\Widget\CompanyMenuItemWidget} instead.
 */
class MenuItemCompanyWidgetPlugin extends AbstractWidgetPlugin
{
    /**
     * @var string
     */
    public const NAME = 'MenuItemCompanyWidgetPlugin';

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function initialize(): void
    {
        $widget = new CompanyMenuItemWidget();

        $this->parameters = $widget->getParameters();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public static function getName(): string
    {
        return static::NAME;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public static function getTemplate(): string
    {
        return CompanyMenuItemWidget::getTemplate();
    }
}
