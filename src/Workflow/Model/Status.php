<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29.10.13
 * Time: 10:26
 */

namespace Workflow\Model;


class Status
{
	const CREATED = 'created';

	const PROPOSED = 'proposed';

	const VALIDATED = 'validated';

	const PUBLISHED = 'published';

	const UNPUBLISHED = 'unpublished';

	const ARCHIVED = 'archived';

	const DELETED = 'deleted';

} 