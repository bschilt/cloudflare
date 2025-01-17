<?php

namespace Drutiny\Cloudflare\Audit;

use Drutiny\Audit\AbstractAnalysis;
use Drutiny\Sandbox\Sandbox;

/**
 * @Token(
 *  name = "invalid_actions",
 *  type = "array",
 *  description = "A keyed list of actions the page rule that don't match the values set in the settings parameter.",
 * )
 */
class AnalyticsAnalysis extends AbstractAnalysis
{
    use ApiEnabledAuditTrait;

    public function configure()
    {
        $this->addParameter(
            'expression',
            static::PARAMETER_OPTIONAL,
            'An expression to evaluate to determine the outcome of the audit',
            ''
        );

    }


    /**
     * {@inheritdoc}
     */
    public function gather(Sandbox $sandbox)
    {
        $uri = $this->target['uri'];
        $host = strpos($uri, 'http') === 0 ? parse_url($uri, PHP_URL_HOST) : $uri;
        $this->set('host', $host);

        $zone  = $this->zoneInfo($this->getParameter('zone', $host));
        $this->set('zone', $zone['name']);

        $response = $this->api()->request(
            "GET", "zones/{$zone['id']}/analytics/dashboard", ['query' => [
            'since' => $sandbox->getReportingPeriodStart()->format(\DateTime::RFC3339),
            'until' => $sandbox->getReportingPeriodEnd()->format(\DateTime::RFC3339),
            ]]
        );

        foreach ($response as $key => $value) {
            $this->set($key, $value);
        }
    }
}

?>
